<?php

class NotificationService {

    private array $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function notifyNewReview(array $review): void {
        if (empty($this->config['brevo_enabled'])) return;
        if (empty($this->config['brevo_api_key']))  return;

        $payload = [
            'sender' => [
                'name'  => $this->config['brevo_from_name']  ?? 'Amazônia 360',
                'email' => $this->config['brevo_from_email'] ?? '',
            ],
            'to' => [
                ['email' => $this->config['notification_email']],
            ],
            'subject'     => $this->subject($review),
            'htmlContent' => $this->html($review),
            'textContent' => $this->text($review),
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'api-key: ' . $this->config['brevo_api_key'],
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function subject(array $r): string {
        $nota  = (int) $r['nota'];
        $emoji = $nota >= 9 ? '🟢' : ($nota >= 7 ? '🟡' : '🔴');
        $nome  = $r['nome'] !== '' ? $r['nome'] : 'Anônimo';
        return "$emoji Nova avaliação NPS: nota $nota — $nome";
    }

    private function html(array $r): string {
        $nota      = (int) $r['nota'];
        $nome      = htmlspecialchars($r['nome'] !== '' ? $r['nome'] : 'Anônimo');
        $comentario= nl2br(htmlspecialchars($r['comentario']));
        $data      = (new DateTime($r['criado_em']))->format('d/m/Y \à\s H\hi');

        if ($nota >= 9)      { $cor = '#2d7a4f'; $rotulo = 'Promotor'; }
        elseif ($nota >= 7)  { $cor = '#b8860b'; $rotulo = 'Neutro'; }
        else                 { $cor = '#922b21'; $rotulo = 'Detrator'; }

        $comentarioBlocos = $r['comentario'] !== ''
            ? "<p style='margin:0 0 8px 0;color:#888;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;'>Sugestão / Crítica</p>
               <p style='margin:0;color:#ccc;font-size:15px;line-height:1.7;'>\"{$comentario}\"</p>"
            : "<p style='color:#666;font-size:14px;font-style:italic;'>Nenhum comentário.</p>";

        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#0a0a0a;font-family:Arial,sans-serif;'>
          <table width='100%' cellpadding='0' cellspacing='0'>
            <tr><td align='center' style='padding:40px 16px;'>
              <table width='600' cellpadding='0' cellspacing='0' style='background:#111;border:1px solid #1e1e1e;border-radius:16px;overflow:hidden;'>

                <!-- Header -->
                <tr>
                  <td style='background:{$cor};padding:24px 36px;'>
                    <p style='margin:0;color:#fff;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.8;'>Nova avaliação</p>
                    <p style='margin:6px 0 0;color:#fff;font-size:28px;font-weight:700;'>Amazônia 360 — NPS</p>
                  </td>
                </tr>

                <!-- Score -->
                <tr>
                  <td style='padding:32px 36px 0;'>
                    <table cellpadding='0' cellspacing='0'>
                      <tr>
                        <td style='background:{$cor};border-radius:12px;width:64px;height:64px;text-align:center;vertical-align:middle;'>
                          <span style='color:#fff;font-size:28px;font-weight:700;'>{$nota}</span>
                        </td>
                        <td style='padding-left:20px;'>
                          <p style='margin:0;color:#888;font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;'>Classificação</p>
                          <p style='margin:4px 0 0;color:{$cor};font-size:20px;font-weight:700;'>{$rotulo}</p>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <!-- Info -->
                <tr>
                  <td style='padding:24px 36px;'>
                    <table width='100%' cellpadding='0' cellspacing='0' style='border-top:1px solid #222;padding-top:24px;'>
                      <tr>
                        <td style='padding-bottom:16px;'>
                          <p style='margin:0 0 4px 0;color:#888;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;'>Identificação</p>
                          <p style='margin:0;color:#f5f5f0;font-size:16px;font-weight:600;'>{$nome}</p>
                        </td>
                        <td style='padding-bottom:16px;text-align:right;'>
                          <p style='margin:0 0 4px 0;color:#888;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;'>Data</p>
                          <p style='margin:0;color:#f5f5f0;font-size:15px;'>{$data}</p>
                        </td>
                      </tr>
                    </table>
                    <div style='border-top:1px solid #222;padding-top:20px;'>
                      {$comentarioBlocos}
                    </div>
                  </td>
                </tr>

                <!-- Footer -->
                <tr>
                  <td style='background:#0a0a0a;padding:16px 36px;border-top:1px solid #1e1e1e;'>
                    <p style='margin:0;color:#444;font-size:12px;'>Enviado automaticamente pelo sistema NPS — Amazônia 360</p>
                  </td>
                </tr>

              </table>
            </td></tr>
          </table>
        </body>
        </html>";
    }

    private function text(array $r): string {
        $nota  = (int) $r['nota'];
        $nome  = $r['nome'] !== '' ? $r['nome'] : 'Anônimo';
        $data  = (new DateTime($r['criado_em']))->format('d/m/Y H:i');
        $linhas = [
            "Nova avaliação NPS — Amazônia 360",
            "---",
            "Nota: $nota/10",
            "Nome: $nome",
            "Data: $data",
        ];
        if ($r['comentario'] !== '') {
            $linhas[] = "---";
            $linhas[] = "Comentário:";
            $linhas[] = $r['comentario'];
        }
        return implode("\n", $linhas);
    }
}
