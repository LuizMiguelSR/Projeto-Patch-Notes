<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PatchNote;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PatchNoteImportController extends Controller
{
    private function domNodesToHtml(array $nodes): string
    {
        $doc = new DOMDocument();
        $container = $doc->createElement('div');
        $doc->appendChild($container);

        foreach ($nodes as $node) {
            $imported = $doc->importNode($node, true);
            $container->appendChild($imported);
        }

        return $doc->saveHTML($container);
    }

    public function import(Request $request)
    {
        $url = env('PATCH_NOTES_DOC_URL');

        $response = Http::get($url);

        if (!$response->ok()) {
            return back()->with('error', 'Erro ao acessar o documento.');
        }

        $html = mb_convert_encoding($response->body(), 'HTML-ENTITIES', 'UTF-8');

        // Parse do HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $body = $dom->getElementsByTagName('body')->item(0);
        $allElements = $body->getElementsByTagName('*');

        $patches = [];
        $currentPatchElements = [];
        $recording = false;
        $tableCaptured = false;

        // 1. Primeiro coletamos todos os patches em ordem
        foreach ($allElements as $element) {
            if ($element->nodeName === 'p' && str_starts_with(trim($element->textContent), '📌Status: Completed')) {
                if ($recording && !empty($currentPatchElements)) {
                    array_unshift($patches, $currentPatchElements); // Adiciona no início do array
                }
                $currentPatchElements = [];
                $recording = true;
                $tableCaptured = false;
                $currentPatchElements[] = $element;
                continue;
            }

            if ($recording && in_array($element->nodeName, ['p', 'ul', 'table'])) {
                if ($element->nodeName === 'table') {
                    if ($tableCaptured) continue;
                    $tableCaptured = true;
                }
                $currentPatchElements[] = $element;
            }
        }

        if ($recording && !empty($currentPatchElements)) {
            array_unshift($patches, $currentPatchElements); // Adiciona o último patch no início
        }

        $importedCount = 0;
        $skippedCount = 0;
        $updatedCount = 0;

        // 2. Agora processamos na ordem correta (do mais antigo para o mais recente)
        foreach ($patches as $patch) {
            $patchHtml = $this->domNodesToHtml($patch);
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML('<?xml encoding="UTF-8">' . $patchHtml);

            $xpath = new DOMXPath($doc);
            $spans = $xpath->query('//span');

            $date = now(); // valor padrão

            foreach ($spans as $i => $span) {
                if (strpos($span->textContent, '📅') !== false) {
                    $next = $span->nextSibling;
                    while ($next && $next->nodeType !== XML_ELEMENT_NODE) {
                        $next = $next->nextSibling;
                    }

                    if ($next && $next->nodeName === 'span') {
                        $dateText = trim($next->textContent);

                        if (preg_match('/([A-Za-z]+\s+\d{1,2}(st|nd|rd|th)?)(?:-\d{1,2}(st|nd|rd|th)?)?,?\s+(\d{4})/', $dateText, $match)) {
                            $rawDate = $match[1] . ' ' . $match[4];
                            $cleanDate = preg_replace('/(\d{1,2})(st|nd|rd|th)/i', '$1', $rawDate);
                            try {
                                $date = Carbon::parse($cleanDate);
                            } catch (\Exception $e) {
                                Log::error("Erro ao fazer parse da data: " . $cleanDate . " - " . $e->getMessage());
                                $date = now();
                            }
                        }
                    }
                    break;
                }
            }

            // Verifica se já existe um patch com esta data
            $existingPatch = PatchNote::whereDate('date', $date->toDateString())->first();

            if (!$existingPatch) {
                PatchNote::create([
                    'date' => $date,
                    'content' => $patchHtml,
                    'status' => 'completed',
                ]);
                $importedCount++;
            } else {
                // Opcional: atualizar se o conteúdo for diferente
                if ($existingPatch->content !== $patchHtml) {
                    $existingPatch->update(['content' => $patchHtml]);
                    $updatedCount++;
                }
                $skippedCount++;
            }
        }

        return back()->with('success', "Importação concluída:
            $importedCount novos patches,
            $updatedCount atualizados,
            $skippedCount já existentes (não modificados).");
    }
}
