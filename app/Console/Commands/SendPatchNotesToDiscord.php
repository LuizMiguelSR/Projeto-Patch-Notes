<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

class SendPatchNotesToDiscord extends Command
{
    protected $signature = 'bot:listen-discord';
    protected $description = 'Fica ouvindo mensagens do canal do Discord e responde com patch notes';

    public function handle()
    {
        $discord = new Discord([
            'token' => env('DISCORD_BOT_TOKEN'),
        ]);

        $discord->on('ready', function (Discord $discord) {
            $this->info("🤖 Bot está online!");

            // Evento de mensagem recebida
            $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
                $command = strtolower(trim($message->content));

                // Exemplo: responder ao comando "!patchnotes"
                if ($command === '!patchnotes') {
                    $this->info("📥 Comando recebido: !patchnotes");

                    $patchNotes = \App\Models\PatchNote::whereDate('created_at', today())->get();

                    if ($patchNotes->isEmpty()) {
                        $message->channel->sendMessage("❌ Nenhum patch note encontrado hoje.");
                        return;
                    }

                    foreach ($patchNotes as $note) {
                        $message->channel->sendMessage("📢 **Patch Note**\n" . $note->content);
                    }
                }
            });
        });

        // Inicia o loop do Discord
        $discord->run();
    }
}
