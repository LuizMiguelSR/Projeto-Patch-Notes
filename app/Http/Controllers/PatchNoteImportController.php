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
    public function runFromCron(Request $request)
    {
        $secret = env('CRON_SECRET');

        if ($request->query('token') !== $secret) {
            Log::warning('Tentativa de acesso não autorizado ao cron de importação.');
            return response('Acesso não autorizado', 403);
        }

        try {
            Log::info('Iniciando importação automática de patch notes via cron...');

            [$importedCount, $updatedCount, $skippedCount] = $this->import($request, true);

            if ($importedCount > 0 || $updatedCount > 0) {
                Log::info("Importação via cron concluída: $importedCount novos, $updatedCount atualizados, $skippedCount já existentes.");
            } else {
                Log::info("Importação via cron executada, mas sem alterações. Todos os $skippedCount patches já existiam.");
            }

            return response('Importação processada via cron.', 200);

        } catch (\Throwable $e) {
            Log::error('Erro durante importação via cron: ' . $e->getMessage());
            return response('Erro ao importar patches.', 500);
        }
    }

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

    public function import(Request $request, bool $silent = false)
    {
        $url = env('PATCH_NOTES_DOC_URL');

        $response = Http::get($url);

        if (!$response->ok()) {
            return back()->with('error', 'Erro ao acessar o documento.');
        }

        $html = mb_convert_encoding($response->body(), 'HTML-ENTITIES', 'UTF-8');

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $body = $dom->getElementsByTagName('body')->item(0);
        $allElements = $body->getElementsByTagName('*');

        $patches = [];
        $currentPatchElements = [];
        $recording = false;
        $tableCaptured = false;

        foreach ($allElements as $element) {
            if ($element->nodeName === 'p' && str_starts_with(trim($element->textContent), '📌Status: Completed')) {
                if ($recording && !empty($currentPatchElements)) {
                    array_unshift($patches, $currentPatchElements);
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
            array_unshift($patches, $currentPatchElements);
        }

        $importedCount = 0;
        $skippedCount = 0;
        $updatedCount = 0;

        foreach ($patches as $patch) {
            $patchHtml = $this->domNodesToHtml($patch);
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML('<?xml encoding="UTF-8">' . $patchHtml);

            $xpath = new DOMXPath($doc);
            $spans = $xpath->query('//span');

            $date = now();

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

            $existingPatch = PatchNote::whereDate('date', $date->toDateString())->first();

            if (!$existingPatch) {
                PatchNote::create([
                    'date' => $date,
                    'content' => $patchHtml,
                    'status' => 'completed',
                ]);
                $importedCount++;
            } else {
                $skippedCount++;
            }
        }

        if ($silent) {
            return [$importedCount, $updatedCount, $skippedCount];
        } else {
            return back()->with('success', "Importação concluída:
            $importedCount novos patches,
            $updatedCount atualizados,
            $skippedCount já existentes (não modificados).");
        }
    }
}
