<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\PatchNote;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FetchPatchNotes extends Command
{
    protected $signature = 'patch:fetch';
    protected $description = 'Busca os patch notes do Google Docs e salva no banco de dados';

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

    public function handle()
    {
        $url = env('PATCH_NOTES_DOC_URL');

        $this->info("Buscando conteúdo do documento...");
        $response = Http::get($url);

        if (!$response->ok()) {
            $this->error("Erro ao acessar o documento.");
            return;
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

        foreach ($allElements as $element) {
            if ($element->nodeName === 'p' && str_starts_with(trim($element->textContent), '📌Status: Completed')) {
                if ($recording && !empty($currentPatchElements)) {
                    $patches[] = $currentPatchElements;
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
            $patches[] = $currentPatchElements;
        }

        $this->info("Encontrados " . count($patches) . " patches. Salvando...");

        foreach ($patches as $patch) {
            $patchHtml = $this->domNodesToHtml($patch);
            // Corrigir problemas de encoding
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML('<?xml encoding="UTF-8">' . $patchHtml);

            $xpath = new DOMXPath($doc);
            $spans = $xpath->query('//span');

            $date = now(); // valor padrão

            foreach ($spans as $i => $span) {
                if (strpos($span->textContent, '📅') !== false) {
                    // Tenta pegar o próximo <span> com a data
                    $next = $span->nextSibling;
                    while ($next && $next->nodeType !== XML_ELEMENT_NODE) {
                        $next = $next->nextSibling;
                    }

                    if ($next && $next->nodeName === 'span') {
                        $dateText = trim($next->textContent);
                        $this->info("📅 Texto encontrado: " . $dateText);

                        // Extrai a data (antes de parênteses ou "/")
                        if (preg_match('/([A-Za-z]+\s+\d{1,2}(st|nd|rd|th)?)(?:-\d{1,2}(st|nd|rd|th)?)?,?\s+(\d{4})/', $dateText, $match)) {
                            $rawDate = $match[1] . ' ' . $match[4];
                            $cleanDate = preg_replace('/(\d{1,2})(st|nd|rd|th)/i', '$1', $rawDate);
                            $this->info("🔍 Data convertida para parse: " . $cleanDate);
                            try {
                                $date = Carbon::parse($cleanDate);
                            } catch (\Exception $e) {
                                $this->error("❌ Erro ao fazer parse da data: " . $cleanDate);
                                $this->error($e->getMessage());
                                $date = now();
                            }
                        } else {
                            $this->warn("⚠️ Não conseguiu extrair a data da string: " . $dateText);
                        }
                    } else {
                        $this->warn("⚠️ Não encontrou <span> com a data após 📅");
                    }

                    break;
                }
            }

            // Salva no banco se ainda não existe
            $exists = PatchNote::whereDate('date', $date->toDateString())->exists();
            if (!$exists) {
                Log::debug('Patch size: ' . strlen($patchHtml));
                Log::debug($patchHtml);
                PatchNote::create([
                    'date' => $date,
                    'content' => $patchHtml,
                    'status' => 'completed',
                ]);
                $this->info("✅ Patch salvo: " . $date->toDateString());
            } else {
                $this->info("⚠️ Patch já existente: " . $date->toDateString());
            }
        }

        $this->info("Finalizado.");
    }

}
