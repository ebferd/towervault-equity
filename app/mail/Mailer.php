<?php
// ============================================================
//  NexVest — Mailer
//  app/mail/Mailer.php
// ============================================================

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    // ── Resolve SMTP config: DB settings win over .env ───────────
    private static function smtpConfig(): array {
        $env = CONFIG['mail'];
        // Admin saves SMTP to platform_settings (group=email). Prefer those over .env values.
        return [
            'host'       => platform_setting('smtp_host',      $env['host']),
            'port'       => (int) platform_setting('smtp_port', $env['port']),
            'secure'     => platform_setting('smtp_secure',     $env['secure']),
            'user'       => platform_setting('smtp_user',       $env['user']),
            'pass'       => platform_setting('smtp_pass',       $env['pass']),
            'from_email' => $env['from_email'] ?: platform_setting('smtp_user', ''),
            'from_name'  => platform_setting('smtp_from_name',  $env['from_name']),
            'support'    => $env['support'],
        ];
    }

    // ── Base send method ──────────────────────────────────────
    private static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
        // Guard: PHPMailer not installed (vendor/ missing on this host) — never fatal, just skip.
        if (!class_exists(PHPMailer::class)) {
            error_log("Mailer error [{$toEmail}]: PHPMailer library not found (composer dependencies missing).");
            return false;
        }

        $cfg = self::smtpConfig();
        if (empty($cfg['host']) || empty($cfg['user']) || empty($cfg['pass'])) {
            error_log("Mailer error [{$toEmail}]: SMTP is not configured.");
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['user'];
            $mail->Password   = $cfg['pass'];
            $mail->SMTPSecure = $cfg['secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $cfg['port'];
            $mail->CharSet    = 'UTF-8';
            // Never let a slow/blocked SMTP port hang the request for minutes
            $mail->Timeout       = 10;
            $mail->SMTPKeepAlive = false;

            $fromEmail = $cfg['from_email'] ?: $cfg['user'];
            $mail->setFrom($fromEmail, $cfg['from_name']);
            $mail->addAddress($toEmail, $toName);
            if (!empty($cfg['support'])) {
                $mail->addReplyTo($cfg['support'], $cfg['from_name']);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(['<br>','<br/>','</p>','</div>'], "\n", $htmlBody));

            $mail->send();
            return true;

        } catch (\Throwable $e) {
            // Catch everything — PHPMailer's own Exception, plus any Error (missing class,
            // type errors, etc.) so a broken mail config can NEVER take down the request.
            error_log("Mailer error [{$toEmail}]: " . $e->getMessage());
            return false;
        }
    }

    // ── Public raw send (for admin use: SMTP test, direct email) ─
    public static function sendRaw(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
        return self::send($toEmail, $toName, $subject, $htmlBody);
    }

    // ── Email wrapper (branded template) ─────────────────────
    // ── Template helpers ──────────────────────────────────────

    /**
     * Header brand cell for emails — the uploaded logo when one is set,
     * otherwise the initials badge. Emails need an absolute image URL.
     */
    private static function brandCell(string $pName, string $pInit): string {
        $logo = (string) platform_setting('platform_logo', '');
        if (trim($logo) !== '') {
            $src = htmlspecialchars(file_url_abs($logo), ENT_QUOTES);
            $alt = htmlspecialchars($pName, ENT_QUOTES);
            return '<td style="width:32px;height:32px;vertical-align:middle">'
                 . '<img src="' . $src . '" width="32" height="32" alt="' . $alt . '" '
                 . 'style="display:block;width:32px;height:32px;border-radius:6px;object-fit:contain"/></td>';
        }
        return '<td style="background:#111827;border-radius:6px;width:32px;height:32px;text-align:center;vertical-align:middle">'
             . '<span style="font-family:Georgia,serif;font-size:11px;font-weight:700;color:#ffffff;line-height:32px;display:block;letter-spacing:-.5px">'
             . htmlspecialchars($pInit) . '</span></td>';
    }

    private static function wrap(string $content, string $preheader = ''): string {
        $pName    = platform_setting('platform_name',    'NexVest');
        $pTagline = platform_setting('platform_tagline', 'Capital Group');
        $pInit    = platform_setting('platform_initials','NV');
        $pEmail   = platform_setting('platform_email',   'noreply@nexvest.com');
        $pSupport = platform_setting('platform_support_email', 'support@nexvest.com');
        $pPhone   = platform_setting('platform_phone',   '');
        $pAddr    = platform_setting('platform_address', '');
        $pUrl     = platform_setting('platform_website', 'https://nexvest.com');

        $pre = $preheader
            ? "<div style='display:none;max-height:0;overflow:hidden;font-size:1px;color:#F4F5F7'>{$preheader}&nbsp;&zwnj;</div>"
            : '';

        $phone = $pPhone ? " &middot; {$pPhone}" : '';
        $addr  = $pAddr  ? "{$pAddr} &middot; " : '';
        $brandCell = self::brandCell($pName, $pInit);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>{$pName}</title>
<style>
body,html{margin:0;padding:0;background:#F4F5F7;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased}
table{border-spacing:0;border-collapse:collapse}
td{padding:0}
a{color:#111827;text-decoration:none}
img{border:0;display:block}
@media only screen and (max-width:600px){
  .outer{padding:16px 12px 32px!important}
  .shell{border-radius:6px!important}
  .body-cell{padding:28px 24px!important}
  .footer-cell{padding:20px 24px!important}
}
</style>
</head>
<body style="margin:0;padding:0;background:#F4F5F7">
{$pre}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7">
<tr><td align="center" class="outer" style="padding:32px 16px 48px">
<table width="580" cellpadding="0" cellspacing="0" class="shell" style="max-width:580px;width:100%;background:#ffffff;border:1px solid #E4E7EE;border-radius:8px;overflow:hidden">

  <!-- HEADER -->
  <tr><td style="padding:28px 40px 24px;border-bottom:1px solid #F0F2F7">
    <table width="100%" cellpadding="0" cellspacing="0"><tr>
      <td valign="middle">
        <table cellpadding="0" cellspacing="0"><tr>
          {$brandCell}
          <td style="padding-left:10px;vertical-align:middle">
            <div style="font-size:14px;font-weight:600;color:#111827;letter-spacing:-.2px">{$pName}</div>
            <div style="font-size:10px;color:#9CA3AF;letter-spacing:.5px;text-transform:uppercase;margin-top:1px">{$pTagline}</div>
          </td>
        </tr></table>
      </td>
    </tr></table>
  </td></tr>

  <!-- BODY -->
  <tr><td class="body-cell" style="padding:36px 40px">{$content}</td></tr>

  <!-- FOOTER -->
  <tr><td class="footer-cell" style="padding:22px 40px;background:#F9FAFB;border-top:1px solid #F0F2F7">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr><td style="padding-bottom:14px;border-bottom:1px solid #F0F2F7">
        <a href="{$pUrl}/investor/dashboard" style="font-size:12px;color:#6B7280;font-weight:500;margin-right:16px;text-decoration:none">Investor Portal</a>
        <a href="{$pUrl}/investor/portfolio" style="font-size:12px;color:#6B7280;font-weight:500;margin-right:16px;text-decoration:none">Portfolio</a>
        <a href="mailto:{$pSupport}" style="font-size:12px;color:#6B7280;font-weight:500;text-decoration:none">Support</a>
      </td></tr>
      <tr><td style="padding-top:14px">
        <div style="font-size:11px;color:#9CA3AF;line-height:1.7">{$addr}{$pEmail}{$phone}</div>
        <div style="font-size:10.5px;color:#C4C9D4;line-height:1.65;margin-top:8px">
          You received this email because you have an account with {$pName}. If you did not initiate this action, please
          <a href="mailto:{$pSupport}" style="color:#9CA3AF">contact support</a> immediately.
          {$pName} will never ask for your password via email.
        </div>
      </td></tr>
    </table>
  </td></tr>

</table>
</td></tr></table>
</body></html>
HTML;
    }

    private static function btn(string $label, string $url, string $color = '#111827'): string {
        return "<table cellpadding='0' cellspacing='0' style='margin:24px 0 6px'>
          <tr><td>
            <a href='{$url}' style='display:inline-block;background:{$color};color:#ffffff;font-size:13px;font-weight:600;text-decoration:none;padding:12px 28px;border-radius:6px;letter-spacing:.1px'>{$label}</a>
          </td></tr>
        </table>";
    }

    private static function alert(string $type, string $text): string {
        $border = ['info'=>'#6B7280','success'=>'#10B981','warning'=>'#F59E0B','danger'=>'#EF4444'];
        $color  = ['info'=>'#4B5563','success'=>'#065F46','warning'=>'#92400E','danger'=>'#991B1B'];
        $b = $border[$type] ?? $border['info'];
        $c = $color[$type]  ?? $color['info'];
        return "<table width='100%' cellpadding='0' cellspacing='0' style='margin:20px 0'>
          <tr><td style='border-left:2px solid {$b};padding:10px 16px;font-size:13px;color:{$c};line-height:1.65'>{$text}</td></tr>
        </table>";
    }

    private static function dataTable(array $rows): string {
        $html = "<table width='100%' cellpadding='0' cellspacing='0' style='margin:20px 0;font-size:13px'>";
        foreach ($rows as [$label, $value]) {
            $html .= "<tr style='border-bottom:1px solid #F3F4F6'>
              <td style='padding:10px 0;color:#9CA3AF;font-weight:500;width:42%'>{$label}</td>
              <td style='padding:10px 0;color:#111827;font-weight:600;text-align:right'>{$value}</td>
            </tr>";
        }
        return $html . "</table>";
    }

    private static function amountCard(string $amount, string $refLabel, string $refValue): string {
        return "<table width='100%' cellpadding='0' cellspacing='0' style='margin:20px 0;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px'>
          <tr>
            <td style='padding:20px 24px;vertical-align:middle'>
              <div style='font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#9CA3AF;margin-bottom:5px'>{$refLabel}</div>
              <div style='font-size:28px;font-weight:700;color:#111827;letter-spacing:-.5px'>{$amount}</div>
            </td>
            <td style='padding:20px 24px;vertical-align:middle;text-align:right'>
              <div style='font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:#9CA3AF;margin-bottom:4px'>Reference</div>
              <div style='font-size:12px;color:#6B7280;font-family:monospace'>{$refValue}</div>
            </td>
          </tr>
        </table>";
    }

    private static function heading(string $text): string {
        return "<div style='font-size:26px;font-weight:700;color:#111827;line-height:1.25;margin-bottom:8px;letter-spacing:-.4px'>{$text}</div>";
    }

    private static function eyebrow(string $text): string {
        return "<div style='font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#6B7280;margin-bottom:12px'>{$text}</div>";
    }

    private static function body(string $text): string {
        return "<div style='font-size:14px;color:#4B5563;line-height:1.75;margin-bottom:0'>{$text}</div>";
    }

    private static function greeting(array $user): string {
        return "<div style='font-size:14px;color:#4B5563;margin-bottom:20px'>Hi {$user['first_name']},</div>";
    }

    private static function divider(): string {
        return "<div style='height:1px;background:#F3F4F6;margin:24px 0'></div>";
    }

    private static function signoff(): string {
        $name    = platform_setting('platform_name',    'NexVest');
        $support = platform_setting('platform_support_email', 'support@nexvest.com');
        return self::divider() .
               "<div style='font-size:13.5px;color:#4B5563;line-height:1.8'>Regards,<br>
               <strong style='color:#111827'>{$name} Team</strong><br>
               <span style='font-size:12px;color:#9CA3AF'>{$support}</span></div>";
    }

    // ─────────────────────────────────────────────────────────
    //  PUBLIC SEND METHODS
    // ─────────────────────────────────────────────────────────

    public static function sendWelcome(array $user): bool {
        $pName = platform_setting('platform_name', 'NexVest');
        $url   = platform_setting('platform_website', 'https://nexvest.com');
        $steps = [
            ['Complete KYC',     'Submit your identity documents to unlock investment access.'],
            ['Fund Your Wallet', 'Deposit via crypto, PayPal, or wire transfer.'],
            ['Start Investing',  'Explore real estate portfolios and index fund products.'],
        ];
        $stepsHtml = implode('', array_map(fn($s, $i) =>
            "<tr><td style='padding:10px 0;border-bottom:1px solid #F3F4F6'>
              <table cellpadding='0' cellspacing='0'><tr>
                <td style='width:28px;height:28px;background:#F3F4F6;border-radius:4px;text-align:center;vertical-align:middle;padding-right:14px'>
                  <span style='font-size:11px;font-weight:700;color:#6B7280;line-height:28px;display:block'>0" . ($i+1) . "</span>
                </td>
                <td><div style='font-size:13px;font-weight:600;color:#111827'>{$s[0]}</div><div style='font-size:12px;color:#9CA3AF;margin-top:2px'>{$s[1]}</div></td>
              </tr></table>
            </td></tr>", $steps, array_keys($steps)));

        $content = self::greeting($user)
            . self::heading("Welcome to {$pName}.")
            . self::body("Your investor account has been created. You now have access to our institutional investment platform featuring curated real estate portfolios and index fund products.")
            . self::alert('info', 'Complete your KYC verification to unlock full investment access.')
            . "<table width='100%' cellpadding='0' cellspacing='0' style='margin:14px 0'>{$stepsHtml}</table>"
            . self::btn('Access Your Dashboard', $url . '/investor/dashboard')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Welcome to {$pName} — Your Account is Ready",
            self::wrap($content, "Welcome to {$pName} — your account is ready."));
    }

    public static function sendEmailVerification(array $user, string $otp): bool {
        $pName = platform_setting('platform_name', 'NexVest');
        $content = self::greeting($user)
            . self::heading('Verify your email address.')
            . self::body("Enter the 6-digit code below to confirm your email address. This code expires in <strong>15 minutes</strong>.")
            . "<table width='100%' cellpadding='0' cellspacing='0' style='margin:22px 0;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px'>
                <tr><td align='center' style='padding:28px 24px'>
                  <div style='font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#9CA3AF;margin-bottom:14px'>Verification Code</div>
                  <div style='font-size:40px;font-weight:700;color:#111827;letter-spacing:12px;font-family:\"Courier New\",monospace'>{$otp}</div>
                  <div style='font-size:11px;color:#9CA3AF;margin-top:14px'>Expires in 15 minutes</div>
                </td></tr>
              </table>"
            . self::alert('warning', "If you did not create a {$pName} account, please ignore this email or contact our support team.")
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Verify your email address — {$pName}",
            self::wrap($content, 'Your verification code — expires in 15 minutes.'));
    }

    public static function sendPasswordReset(array $user, string $token): bool {
        $url   = rtrim(CONFIG['app']['url'] ?? platform_setting('platform_website', 'https://nexvest.com'), '/');
        $pName = platform_setting('platform_name', 'NexVest');
        $link  = "{$url}/reset-password?token={$token}";
        $exp   = CONFIG['security']['reset_expire_minutes'];

        $content = self::greeting($user)
            . self::heading('Reset your password.')
            . self::body("We received a request to reset your {$pName} account password. Click the button below to set a new password. This link expires in <strong>{$exp} minutes</strong>.")
            . self::btn('Reset My Password', $link)
            . self::alert('warning', "If you didn't request a password reset, please ignore this email. Your current password will remain unchanged.")
            . "<div style='font-size:12px;color:#9CA3AF;margin-top:14px'>If the button doesn't work, copy and paste this link:<br><span style='color:#6B7280;word-break:break-all'>{$link}</span></div>"
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Reset your {$pName} password",
            self::wrap($content, "Reset your {$pName} password — link expires in {$exp} minutes."));
    }

    public static function sendPasswordChanged(array $user): bool {
        $pName = platform_setting('platform_name', 'NexVest');
        $url   = platform_setting('platform_website', 'https://nexvest.com');
        $content = self::greeting($user)
            . self::heading('Password changed.')
            . self::alert('success', 'Your account password was successfully changed.')
            . self::dataTable([['Date & Time', date('F j, Y g:i A T')], ['IP Address', get_ip()]])
            . self::alert('danger', 'If you did not make this change, please contact our support team immediately.')
            . self::btn('Contact Support', $url . '/support')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Your {$pName} password was changed",
            self::wrap($content, "Your password was changed — if this wasn't you, act now."));
    }

    public static function sendTwoFAEnabled(array $user): bool {
        $pName = platform_setting('platform_name', 'NexVest');
        $content = self::greeting($user)
            . self::heading('Two-factor authentication enabled.')
            . self::alert('success', 'Two-factor authentication (2FA) has been successfully enabled on your account. Your account is now significantly more secure.')
            . self::body("Each time you sign in, you will be prompted to enter a 6-digit code from your authenticator app in addition to your password.")
            . self::alert('warning', 'Save your backup codes in a secure location. If you lose access to your authenticator app, you will need a backup code to recover your account.')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Two-Factor Authentication enabled on your account",
            self::wrap($content, "2FA has been enabled on your {$pName} account."));
    }

    public static function sendLoginAlert(array $user, string $ip, string $ua): bool {
        // Throttle: only send if last alert was more than 24h ago to avoid flooding
        $lastAlert = DB::fetch(
            "SELECT created_at FROM notifications WHERE user_id=? AND type='login_alert' ORDER BY created_at DESC LIMIT 1",
            [$user['id']]
        );
        if ($lastAlert && (time() - strtotime($lastAlert['created_at'])) < 86400) {
            return true; // Silently skip — already alerted recently
        }

        $pName = platform_setting('platform_name', 'NexVest');
        $url   = platform_setting('platform_website', 'https://nexvest.com');
        $content = self::greeting($user)
            . self::heading('New login detected.')
            . self::body("A new login was detected on your {$pName} account.")
            . self::dataTable([['Date & Time', date('F j, Y g:i A T')], ['IP Address', $ip], ['Browser/Device', substr($ua, 0, 80)]])
            . self::alert('warning', "If this was you, no action is needed. If you don't recognise this login, please change your password immediately.")
            . self::btn('Change Password', $url . '/forgot-password')
            . "<div style='margin-top:10px'>" . self::btn('Contact Support', $url . '/support') . "</div>"
            . self::signoff();

        // Log the alert as a notification
        DB::query("INSERT INTO notifications (user_id, type, title, message) VALUES (?,'login_alert','New Login Detected','A new login was detected from IP {$ip}.')", [$user['id']]);

        return self::send($user['email'], $user['first_name'], "New login on your {$pName} account",
            self::wrap($content, "New login detected — verify it was you."));
    }

    public static function sendKycSubmitted(array $user, string $ref): bool {
        $pName = platform_setting('platform_name', 'NexVest');
        $content = self::greeting($user)
            . self::heading('Documents received.')
            . self::body("Thank you for submitting your identity documents. Our compliance team will review your submission within <strong>24–48 business hours</strong>.")
            . self::dataTable([['Submission Date', date('F j, Y')], ['Reference', $ref], ['Status', 'Under Review']])
            . self::alert('info', 'You will receive an email notification once your documents have been reviewed.')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Your identity documents have been received — {$pName}",
            self::wrap($content, "We've received your KYC documents — review in progress."));
    }

    public static function sendKycApproved(array $user): bool {
        $pName = platform_setting('platform_name', 'NexVest');
        $url   = platform_setting('platform_website', 'https://nexvest.com');
        $content = self::greeting($user)
            . self::heading('Identity verified.')
            . self::alert('success', 'Your identity has been successfully verified. Your account now has full access to all investment products.')
            . self::btn('Start Investing', $url . '/investor/investments')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "KYC Verified — Your account is fully activated",
            self::wrap($content, "Your KYC is approved — you're fully verified."));
    }

    public static function sendKycRejected(array $user, string $reason): bool {
        $pName = platform_setting('platform_name', 'NexVest');
        $url   = platform_setting('platform_website', 'https://nexvest.com');
        $content = self::greeting($user)
            . self::heading('Verification unsuccessful.')
            . self::body("Unfortunately, we were unable to verify your identity using the documents submitted.")
            . self::alert('danger', "<strong>Reason:</strong> {$reason}")
            . self::btn('Resubmit Documents', $url . '/investor/kyc')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Action Required: KYC verification unsuccessful",
            self::wrap($content, "Action required — your KYC verification was unsuccessful."));
    }

    public static function sendInvestmentConfirmed(array $user, array $holding, array $investment): bool {
        $sym       = platform_setting('platform_symbol', '$');
        $url       = platform_setting('platform_website', 'https://nexvest.com');
        // ROI is the total return over the full duration
        $totalReturn = (float)$holding['amount'] * (float)$investment['roi'] / 100;
        $fmtInvested = $sym . number_format((float)$holding['amount'], 2);

        $content = self::greeting($user)
            . self::eyebrow('Investment')
            . self::heading('Your investment is confirmed.')
            . self::body('Your position is now active and returns will begin accruing from today.')
            . self::amountCard($fmtInvested, 'Amount invested', $holding['certificate_ref'] ?? '—')
            . self::dataTable([
                ['Investment',    $investment['name']],
                ['Total ROI',     $investment['roi'] . '% over ' . $investment['duration_value'] . ' ' . $investment['duration_unit']],
                ['Duration',      $investment['duration_value'] . ' ' . $investment['duration_unit']],
                ['Expected return', $sym . number_format($totalReturn, 2)],
                ['Start date',    $holding['start_date']],
                ['Maturity date', $holding['end_date']],
              ])
            . self::btn('View portfolio', $url . '/investor/portfolio')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Investment confirmed — {$investment['name']}",
            self::wrap($content, "Your investment in {$investment['name']} is now active."));
    }

    public static function sendTransferReceived(array $receiver, array $sender, float $amount, string $reference, string $note, float $newBalance): bool {
        $sym    = platform_setting('platform_symbol', '$');
        $url    = platform_setting('platform_website', 'https://nexvest.com');
        $pName  = platform_setting('platform_name', 'NexVest');
        $fmtAmt = $sym . number_format($amount, 2);
        $fmtBal = $sym . number_format($newBalance, 2);
        $senderName = htmlspecialchars($sender['first_name'] . ' ' . substr($sender['last_name'], 0, 1) . '.');

        $content = self::greeting($receiver)
            . self::eyebrow('Funds received')
            . self::heading("You've received a transfer.")
            . self::body("Great news — a transfer has been credited to your wallet. The funds are available immediately and can be used to invest or request a withdrawal.")
            . self::amountCard($fmtAmt, 'Amount received', $reference)
            . self::dataTable([
                ['From',        $senderName . ' (fellow investor)'],
                ['Date',        date('d M Y · g:i A')],
                ['New balance', $fmtBal],
                ['Status',      'Completed'],
              ])
            . ($note ? self::alert('info', 'Transfer note: ' . htmlspecialchars($note)) : '')
            . self::body("Your updated wallet balance is <strong style='color:#111827'>{$fmtBal}</strong>. You can invest or withdraw at any time from your dashboard.")
            . self::btn('View My Wallet', $url . '/investor/wallet')
            . self::signoff();

        return self::send(
            $receiver['email'],
            $receiver['first_name'],
            "You received {$fmtAmt} — {$pName}",
            self::wrap($content, "You've received a {$fmtAmt} transfer from a fellow investor.")
        );
    }

    public static function sendReturnCredited(array $user, float $amount, string $source): bool {
        $sym    = platform_setting('platform_symbol', '$');
        $url    = platform_setting('platform_website', 'https://nexvest.com');
        $fmtAmt = $sym . number_format((float)$amount, 2);

        $content = self::greeting($user)
            . self::eyebrow('Return')
            . self::heading('A return has been credited.')
            . self::body('Your investment return has been credited to your wallet and is available immediately.')
            . self::amountCard('+' . $fmtAmt, 'Amount credited', date('F j, Y'))
            . self::dataTable([['Source', $source]])
            . self::btn('View wallet', $url . '/investor/wallet')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Return credited — +{$fmtAmt}",
            self::wrap($content, "+{$fmtAmt} return credited to your wallet."));
    }

    public static function sendDepositConfirmed(array $user, array $invoice): bool {
        $sym    = platform_setting('platform_symbol', '$');
        $url    = platform_setting('platform_website', 'https://nexvest.com');
        $fmtAmt = $sym . number_format((float)$invoice['amount'], 2);

        $content = self::greeting($user)
            . self::eyebrow('Deposit')
            . self::heading('Your deposit has been confirmed.')
            . self::body('Your funds have been verified and credited to your wallet. You can start investing immediately.')
            . self::amountCard($fmtAmt, 'Amount credited', $invoice['reference'])
            . self::dataTable([
                ['Method',    ucfirst($invoice['method'])],
                ['Date',      date('F j, Y')],
                ['Status',    'Confirmed'],
              ])
            . self::alert('success', 'Your wallet balance has been updated. Browse active investment opportunities to put your capital to work.')
            . self::btn('Browse investments', $url . '/investor/investments')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Deposit confirmed — {$fmtAmt} credited to your wallet",
            self::wrap($content, "Your deposit of {$fmtAmt} has been confirmed."));
    }

    public static function sendDepositSubmitted(array $user, array $invoice): bool {
        $sym    = platform_setting('platform_symbol', '$');
        $pName  = platform_setting('platform_name', 'NexVest');
        $fmtAmt = $sym . number_format((float)$invoice['amount'], 2);

        $content = self::greeting($user)
            . self::eyebrow('Deposit')
            . self::heading('Payment submitted — under review.')
            . self::body('We have received notification of your payment. Our team will verify it shortly. Once confirmed, your funds will be credited to your wallet.')
            . self::amountCard($fmtAmt, 'Amount submitted', $invoice['reference'])
            . self::dataTable([
                ['Method',    ucfirst($invoice['method'])],
                ['Submitted', date('F j, Y, g:i a')],
                ['Status',    'Under review'],
              ])
            . self::alert('info', 'Quote reference <strong>' . htmlspecialchars($invoice['reference']) . '</strong> if you contact support about this deposit.')
            . self::signoff();

        return self::send($user['email'], $user['first_name'],
            "Deposit submitted — {$fmtAmt} under review",
            self::wrap($content, "Your deposit of {$fmtAmt} is being reviewed."));
    }

    public static function sendWithdrawalSubmitted(array $user, array $wr): bool {
        $sym    = platform_setting('platform_symbol', '$');
        $fmtAmt = $sym . number_format((float)$wr['amount'], 2);

        $content = self::greeting($user)
            . self::eyebrow('Withdrawal')
            . self::heading('Withdrawal request received.')
            . self::body('We have received your withdrawal request. Our team will process it within 3–5 business days.')
            . self::amountCard($fmtAmt, 'Amount requested', $wr['reference'])
            . self::dataTable([
                ['Method', ucfirst($wr['method'])],
                ['Date',   date('F j, Y')],
                ['Status', 'Pending review'],
              ])
            . self::alert('info', 'You will receive an email when your withdrawal is approved and again when funds are sent.')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Withdrawal request received — {$fmtAmt}",
            self::wrap($content, "Your withdrawal request of {$fmtAmt} is under review."));
    }

    public static function sendWithdrawalApproved(array $user, array $wr): bool {
        $sym    = platform_setting('platform_symbol', '$');
        $fmtAmt = $sym . number_format((float)$wr['amount'], 2);

        $content = self::greeting($user)
            . self::eyebrow('Withdrawal')
            . self::heading('Withdrawal approved.')
            . self::body('Your withdrawal request has been approved. Funds will be transferred within 1–2 business days.')
            . self::amountCard($fmtAmt, 'Approved amount', $wr['reference'])
            . self::dataTable([
                ['Method',      ucfirst($wr['method'])],
                ['Approved on', date('F j, Y')],
              ])
            . self::alert('success', 'No further action is needed. You will receive a final confirmation once the transfer is complete.')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Withdrawal approved — {$fmtAmt}",
            self::wrap($content, "Your withdrawal of {$fmtAmt} is approved."));
    }

    public static function sendWithdrawalCompleted(array $user, array $wr): bool {
        $sym    = platform_setting('platform_symbol', '$');
        $fmtAmt = $sym . number_format((float)$wr['amount'], 2);
        $url    = platform_setting('platform_website', 'https://nexvest.com');

        $content = self::greeting($user)
            . self::eyebrow('Withdrawal')
            . self::heading('Withdrawal completed.')
            . self::body('Your funds have been transferred. Please allow up to 2 business days for the amount to appear in your account depending on your bank.')
            . self::amountCard($fmtAmt, 'Amount transferred', $wr['reference'])
            . self::dataTable([
                ['Method',    ucfirst($wr['method'])],
                ['Completed', date('F j, Y')],
              ])
            . self::alert('success', 'If funds do not arrive within 5 business days, contact support with your reference number.')
            . self::btn('Contact support', $url . '/investor/support')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Withdrawal completed — {$fmtAmt} sent",
            self::wrap($content, "Your withdrawal of {$fmtAmt} has been completed."));
    }

    public static function sendTicketReceived(array $user, array $ticket): bool {
        $url = platform_setting('platform_website', 'https://nexvest.com');

        $content = self::greeting($user)
            . self::eyebrow('Support')
            . self::heading('We received your message.')
            . self::body('Thank you for reaching out. Our support team will respond within 24 hours.')
            . self::dataTable([
                ['Ticket ref', $ticket['reference']],
                ['Subject',    $ticket['subject']],
                ['Date',       date('F j, Y')],
                ['Status',     'Open — awaiting response'],
              ])
            . self::btn('View ticket', $url . '/investor/support')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Support ticket received — {$ticket['reference']}",
            self::wrap($content, "We received your support ticket and will reply within 24 hours."));
    }

    public static function sendTicketReplied(array $user, array $ticket, string $replyText): bool {
        $url = platform_setting('platform_website', 'https://nexvest.com');
        $content = self::greeting($user)
            . self::eyebrow('Support')
            . self::heading('We replied to your ticket.')
            . self::dataTable([['Ticket ref', $ticket['reference']], ['Subject', $ticket['subject']]])
            . "<table width='100%' cellpadding='0' cellspacing='0' style='margin:20px 0'>
                <tr><td style='border-left:2px solid #E5E7EB;padding:12px 16px;background:#F9FAFB'>
                  <div style='font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#9CA3AF;margin-bottom:8px'>Support team reply</div>
                  <div style='font-size:13.5px;color:#111827;line-height:1.75'>" . nl2br(htmlspecialchars($replyText)) . "</div>
                </td></tr>
              </table>"
            . self::btn('View full conversation', $url . '/investor/support')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Reply to your support ticket — {$ticket['reference']}",
            self::wrap($content, "New reply on your support ticket {$ticket['reference']}."));
    }

    public static function sendReferralSignup(array $referrer, array $referred): bool {
        $pName = platform_setting('platform_name', 'NexVest');
        $url   = platform_setting('platform_website', 'https://nexvest.com');
        $refName = $referred['first_name'] . ' ' . $referred['last_name'];

        $content = self::greeting($referrer)
            . self::eyebrow('Referral')
            . self::heading('Someone joined using your link.')
            . self::body("Your commission will be credited once <strong>{$refName}</strong> makes their first qualifying investment.")
            . self::dataTable([['Referred investor', $refName], ['Date joined', date('F j, Y')], ['Commission', 'Pending first investment']])
            . self::alert('info', 'You will receive an email as soon as commission is credited to your wallet.')
            . self::btn('View referral dashboard', $url . '/investor/referrals')
            . self::signoff();

        return self::send($referrer['email'], $referrer['first_name'], "Someone joined {$pName} using your referral!",
            self::wrap($content, "{$refName} just joined {$pName} using your referral link!"));
    }

    public static function sendReferralCommission(array $referrer, array $referred, float $commission): bool {
        $sym    = platform_setting('platform_symbol', '$');
        $pName  = platform_setting('platform_name', 'NexVest');
        $url    = platform_setting('platform_website', 'https://nexvest.com');
        $refName = $referred['first_name'] . ' ' . $referred['last_name'];
        $fmtCom = $sym . number_format((float)$commission, 2);

        $content = self::greeting($referrer)
            . self::eyebrow('Referral')
            . self::heading('Commission credited to your wallet.')
            . self::amountCard('+' . $fmtCom, 'Commission earned', date('F j, Y'))
            . self::dataTable([
                ['Referred investor', $refName],
                ['Commission',        $fmtCom],
                ['Date',              date('F j, Y')],
              ])
            . self::btn('View referral dashboard', $url . '/investor/referrals')
            . self::signoff();

        return self::send($referrer['email'], $referrer['first_name'], "Referral commission of {$fmtCom} credited to your wallet",
            self::wrap($content, "+{$fmtCom} referral commission credited!"));
    }

    public static function sendPartnerUpgrade(array $user, float $rate): bool {
        $pName    = platform_setting('platform_name', 'NexVest');
        $url      = rtrim(platform_setting('platform_website', 'https://nexvest.com'), '/');
        $stdRate  = rtrim(rtrim(number_format((float)platform_setting('referral_commission','5'),2),'0'),'.');
        $fmtRate  = rtrim(rtrim(number_format($rate,2),'0'),'.') . '%';

        $content = self::greeting($user)
            . self::eyebrow('Partner Program')
            . self::heading('You&rsquo;ve been upgraded to Partner.')
            . self::body("Your account has been upgraded to <strong>Partner</strong> status. From now on you earn an elevated commission on every investment made by people who join {$pName} through your referral link.")
            . self::amountCard($fmtRate, 'Your Partner commission rate', 'Standard rate is ' . $stdRate . '%')
            . self::body("Your referral link stays the same, and your elevated rate applies to referrals going forward. Track everyone you refer and your commission from the Partner area of your portal.")
            . self::btn('View your Partner dashboard', $url . '/investor/referrals')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "You're now a {$pName} Partner",
            self::wrap($content, "You've been upgraded to Partner — {$fmtRate} referral commission."));
    }

    // ═══ Lifecycle / reminder emails ═══════════════════════════

    private static function reminderUrl(): string {
        return rtrim(platform_setting('platform_website', 'https://nexvest.com'), '/');
    }

    public static function sendDormantReminder(array $user, float $wallet, int $activeCount, float $totalReturns): bool {
        $sym = platform_setting('platform_symbol', '$'); $url = self::reminderUrl();
        $content = self::greeting($user)
            . self::eyebrow('Your account')
            . self::heading("It's been a while.")
            . self::body("You haven't signed in for a while, but your account has been working the whole time. Your active investments are still earning on schedule and your balance is safe. Take a moment to see where things stand.")
            . self::dataTable([
                ['Wallet balance', $sym . number_format($wallet, 2)],
                ['Active investments', (string) $activeCount],
                ['Returns earned to date', '+' . $sym . number_format($totalReturns, 2)],
              ])
            . self::btn('Sign in to your account', $url . '/login')
            . self::signoff();
        return self::send($user['email'], $user['first_name'], "It's been a while — your investments are still earning",
            self::wrap($content, 'Your investments are still earning. Sign back in to check your portfolio.'));
    }

    public static function sendVerifyReminder(array $user, string $otp): bool {
        $url = self::reminderUrl();
        $codeCard = "<table width='100%' cellpadding='0' cellspacing='0' style='margin:20px 0;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px'><tr><td style='padding:20px 24px;text-align:center'>"
            . "<div style='font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#9CA3AF;margin-bottom:8px'>Your verification code</div>"
            . "<div style='font-size:28px;font-weight:700;letter-spacing:8px;color:#111827'>{$otp}</div></td></tr></table>";
        $content = self::greeting($user)
            . self::eyebrow('Action needed')
            . self::heading('Confirm your email address')
            . self::body("Your account is created, but your email isn't verified yet. Verifying secures your account and unlocks deposits, investing, and withdrawals. It only takes a moment.")
            . $codeCard
            . self::btn('Verify my email', $url . '/verify-email?email=' . urlencode($user['email']))
            . self::divider()
            . "<div style='font-size:12.5px;color:#9CA3AF'>Didn't create this account? You can safely ignore this email.</div>";
        return self::send($user['email'], $user['first_name'], 'Confirm your email to finish opening your account',
            self::wrap($content, 'Verify your email to unlock deposits, investing and withdrawals.'));
    }

    public static function sendFinishSetupReminder(array $user, array $items): bool {
        $url = self::reminderUrl();
        $rows = "<table width='100%' cellpadding='0' cellspacing='0' style='margin:18px 0'>";
        foreach ($items as [$label, $done]) {
            $ic = $done
                ? "<td width='28' valign='middle'><div style='width:20px;height:20px;border-radius:50%;background:#DCFCE7;color:#0f7a4a;font-size:11px;font-weight:700;text-align:center;line-height:20px'>&#10003;</div></td>"
                : "<td width='28' valign='middle'><div style='width:18px;height:18px;border-radius:50%;border:1px dashed #D1D5DB;background:#F3F4F6'></div></td>";
            $col = $done ? '#9CA3AF' : '#111827';
            $rows .= "<tr>{$ic}<td valign='middle' style='padding:9px 0 9px 8px;border-bottom:1px solid #F3F4F6;font-size:13.5px;color:{$col}'>{$label}</td></tr>";
        }
        $rows .= "</table>";
        $content = self::greeting($user)
            . self::eyebrow('Getting started')
            . self::heading("You're almost there.")
            . self::body("You're a couple of quick steps away from your first investment. Here's what's left:")
            . $rows
            . self::btn('Complete my setup', $url . '/investor/dashboard', '#0f7a4a')
            . self::signoff();
        return self::send($user['email'], $user['first_name'], 'Finish setting up your investment account',
            self::wrap($content, "You're almost ready to make your first investment."));
    }

    public static function sendAbandonedDepositReminder(array $user, array $invoice): bool {
        $sym = platform_setting('platform_symbol', '$'); $url = self::reminderUrl();
        $method = ucfirst((string) ($invoice['method'] ?? 'transfer'));
        $ref    = htmlspecialchars($invoice['reference'] ?? '');
        $card = "<table width='100%' cellpadding='0' cellspacing='0' style='margin:20px 0;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px'><tr><td style='padding:18px 22px'>"
            . "<div style='font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#9CA3AF;margin-bottom:4px'>Amount &middot; {$method}</div>"
            . "<div style='font-size:24px;font-weight:700;color:#111827'>" . $sym . number_format((float) $invoice['amount'], 2) . "</div>"
            . "<div style='font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#9CA3AF;margin:10px 0 2px'>Reference</div>"
            . "<div style='font-family:monospace;font-size:13px;font-weight:700;color:#111827'>{$ref}</div></td></tr></table>";
        $content = self::greeting($user)
            . self::eyebrow('Pending deposit')
            . self::heading('Your deposit is waiting')
            . self::body("You started a deposit but didn't finish. The details are still ready — complete it to add the funds to your wallet and start investing.")
            . $card
            . self::btn('Complete my deposit', $url . '/investor/wallet', '#B45309')
            . "<table width='100%' cellpadding='0' cellspacing='0' style='margin:16px 0 0;background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px'><tr><td style='padding:12px 14px;font-size:12.5px;color:#92400E;line-height:1.6'>If you've already sent this transfer, no action is needed — it'll be credited once confirmed.</td></tr></table>";
        return self::send($user['email'], $user['first_name'], 'Your deposit is waiting to be completed',
            self::wrap($content, 'Complete your pending deposit to add funds to your wallet.'));
    }

    public static function sendKycAttentionReminder(array $user, string $reason): bool {
        $url = self::reminderUrl();
        $reasonBox = $reason !== ''
            ? "<table width='100%' cellpadding='0' cellspacing='0' style='margin:16px 0;background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px'><tr><td style='padding:12px 14px;font-size:12.5px;color:#92400E;line-height:1.6'><b>Reason:</b> " . htmlspecialchars($reason) . "</td></tr></table>"
            : '';
        $content = self::greeting($user)
            . self::eyebrow('Identity verification')
            . self::heading('Your verification needs attention')
            . self::body("We couldn't verify your identity from your last submission, so some features are on hold. Re-submitting takes a couple of minutes and keeps investing and withdrawals open on your account.")
            . $reasonBox
            . self::btn('Update my verification', $url . '/investor/kyc', '#B45309')
            . self::signoff();
        return self::send($user['email'], $user['first_name'], 'Action needed: your identity verification',
            self::wrap($content, 'Re-submit your identity verification to keep investing and withdrawals open.'));
    }

    public static function sendMonthlyEarningsSummary(array $user, string $monthName, float $returns, float $invested, int $positions, float $commission, float $wallet): bool {
        $sym = platform_setting('platform_symbol', '$'); $url = self::reminderUrl();
        $content = self::greeting($user)
            . self::eyebrow('Your month in review')
            . self::heading($monthName . ' at a glance')
            . self::body('Here is how your money performed last month.')
            . self::amountCard('+' . $sym . number_format($returns, 2), 'Returns earned in ' . $monthName, date('F Y'))
            . self::dataTable([
                ['Total invested', $sym . number_format($invested, 2)],
                ['Active positions', (string) $positions],
                ['Referral commission', '+' . $sym . number_format($commission, 2)],
                ['Wallet balance', $sym . number_format($wallet, 2)],
              ])
            . self::btn('View full portfolio', $url . '/investor/portfolio')
            . self::signoff();
        return self::send($user['email'], $user['first_name'], "Your {$monthName} earnings summary",
            self::wrap($content, "You earned {$sym}" . number_format($returns, 2) . " in {$monthName}. Here's your summary."));
    }

    public static function sendReferralNudge(array $user, float $earned, string $refCode, float $rate): bool {
        $sym = platform_setting('platform_symbol', '$'); $url = self::reminderUrl();
        $rateStr = rtrim(rtrim(number_format($rate, 2), '0'), '.') . '%';
        $link = $url . '/register?ref=' . urlencode($refCode);
        $linkBox = "<table width='100%' cellpadding='0' cellspacing='0' style='margin:16px 0;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px'><tr><td style='padding:12px 14px;font-family:monospace;font-size:12px;color:#4B5563;word-break:break-all'>" . htmlspecialchars($link) . "</td></tr></table>";
        $content = self::greeting($user)
            . self::eyebrow('Referral program')
            . self::heading("Earn {$rateStr} when a friend invests")
            . self::body("You've earned <b>" . $sym . number_format($earned, 2) . "</b> from referrals so far. Share your link and earn <b>{$rateStr} commission</b> on the first investment of anyone who joins through it — paid straight to your wallet.")
            . $linkBox
            . self::btn('Share your link', $url . '/investor/referrals', '#8f7230')
            . self::signoff();
        return self::send($user['email'], $user['first_name'], "Earn {$rateStr} for every friend who invests",
            self::wrap($content, "Share your referral link and earn {$rateStr} on their first investment."));
    }

    public static function sendAnnouncement(array $user, string $subject, string $message): bool {
        $pName = platform_setting('platform_name', 'NexVest');
        $content = self::greeting($user)
            . self::eyebrow('Announcement')
            . self::heading(htmlspecialchars($subject))
            . "<div style='font-size:14px;color:#374151;line-height:1.75;margin:16px 0 24px'>" . nl2br(htmlspecialchars($message)) . "</div>"
            . self::divider()
            . self::alert('info', 'If you have any questions about this update, please contact our support team.')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Important update from {$pName}",
            self::wrap($content, "Important update from {$pName}."));
    }

    // ── Investment Terminated (investor early exit) ────────────
    public static function sendInvestmentTerminated(array $user, array $holding, float $payout): bool {
        $sym    = platform_setting('platform_symbol', '$');
        $pName  = platform_setting('platform_name',   'NexVest');
        $pUrl   = platform_setting('platform_website','https://nexvest.com');
        $fmtPay = $sym . number_format((float)$payout, 2);

        $content = self::greeting($user)
            . self::heading('Investment terminated.')
            . self::body("Your investment position in <strong>{$holding['inv_name']}</strong> has been closed and your principal has been returned to your wallet.")
            . self::dataTable([
                ['Investment',       $holding['inv_name']],
                ['Principal Returned', $fmtPay],
                ['Interest Paid During Term', $sym . number_format((float)$holding['total_earned'], 2)],
                ['Date',             date('F j, Y')],
              ])
            . self::alert('success', 'Your wallet has been credited with ' . $fmtPay . ' (principal). Interest paid during the term is yours to keep. You can reinvest at any time.')
            . self::btn('View Wallet', $pUrl . '/investor/wallet')
            . self::signoff();

        return self::send($user['email'], $user['first_name'], "Investment Closed — {$fmtPay} returned to wallet",
            self::wrap($content, "{$fmtPay} has been returned to your wallet after closing your position."));
    }

    // ─────────────────────────────────────────────────────────
    //  ADMIN NOTIFICATION EMAILS
    // ─────────────────────────────────────────────────────────

    private static function adminEmail(): string {
        return platform_setting('admin_notification_email',
               platform_setting('smtp_user', CONFIG['mail']['user'] ?? ''));
    }

    private static function adminNotify(string $subject, string $htmlContent): bool {
        $email = self::adminEmail();
        if (!$email) return false;
        $pName = platform_setting('platform_name', 'NexVest');
        return self::send($email, "{$pName} Admin", $subject, $htmlContent);
    }

    private static function adminWrap(string $content, string $actionLabel = ''): string {
        $pName = platform_setting('platform_name',    'NexVest');
        $pTagline = platform_setting('platform_tagline', 'Capital Group');
        $pInit = platform_setting('platform_initials','NV');
        $pUrl  = platform_setting('platform_website', 'https://nexvest.com');

        $strip = $actionLabel
            ? "<tr><td style='background:#FFFBEB;border-bottom:1px solid #FDE68A;padding:9px 40px;font-size:11px;font-weight:600;color:#92400E;letter-spacing:.3px'>&#9873; &nbsp;{$actionLabel}</td></tr>"
            : '';
        $brandCell = self::brandCell($pName, $pInit);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>{$pName} Admin</title>
<style>
body,html{margin:0;padding:0;background:#F4F5F7;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased}
table{border-spacing:0;border-collapse:collapse}td{padding:0}a{text-decoration:none}
@media only screen and (max-width:600px){.outer{padding:16px 12px 32px!important}.body-cell{padding:24px!important}.footer-cell{padding:18px 24px!important}}
</style>
</head>
<body style="margin:0;padding:0;background:#F4F5F7">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7">
<tr><td align="center" class="outer" style="padding:32px 16px 48px">
<table width="580" cellpadding="0" cellspacing="0" style="max-width:580px;width:100%;background:#ffffff;border:1px solid #E4E7EE;border-radius:8px;overflow:hidden">

  <!-- HEADER -->
  <tr><td style="padding:28px 40px 24px;border-bottom:1px solid #F0F2F7">
    <table width="100%" cellpadding="0" cellspacing="0"><tr>
      <td valign="middle">
        <table cellpadding="0" cellspacing="0"><tr>
          {$brandCell}
          <td style="padding-left:10px;vertical-align:middle">
            <div style="font-size:14px;font-weight:600;color:#111827;letter-spacing:-.2px">{$pName}</div>
            <div style="font-size:10px;color:#9CA3AF;letter-spacing:.5px;text-transform:uppercase;margin-top:1px">{$pTagline}</div>
          </td>
        </tr></table>
      </td>
      <td align="right" valign="middle">
        <div style="background:#F3F4F6;color:#6B7280;font-size:10px;font-weight:600;padding:3px 10px;border-radius:4px;letter-spacing:.5px;text-transform:uppercase">Admin Alert</div>
      </td>
    </tr></table>
  </td></tr>

  {$strip}

  <!-- BODY -->
  <tr><td class="body-cell" style="padding:32px 40px">{$content}</td></tr>

  <!-- FOOTER -->
  <tr><td class="footer-cell" style="padding:20px 40px;background:#F9FAFB;border-top:1px solid #F0F2F7">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr><td style="padding-bottom:12px;border-bottom:1px solid #F0F2F7">
        <a href="{$pUrl}/admin/dashboard" style="font-size:12px;color:#6B7280;font-weight:500;margin-right:16px">Admin Dashboard</a>
        <a href="{$pUrl}/admin/audit" style="font-size:12px;color:#6B7280;font-weight:500">Audit Log</a>
      </td></tr>
      <tr><td style="padding-top:12px">
        <div style="font-size:10.5px;color:#C4C9D4;line-height:1.65">This alert is system-generated and sent only to the registered admin notification address. All admin actions are recorded in the audit log. Do not reply to this email.</div>
      </td></tr>
    </table>
  </td></tr>

</table>
</td></tr></table>
</body></html>
HTML;
    }

    public static function notifyAdminNewUser(array $user): bool {
        $pUrl = platform_setting('platform_website','https://nexvest.com');
        $name = trim($user['first_name'] . ' ' . $user['last_name']);
        $content = self::heading('New Investor Registration')
            . self::body("A new investor has registered on the platform and is awaiting onboarding.")
            . self::dataTable([
                ['Name',       $name],
                ['Email',      $user['email']],
                ['Country',    $user['country'] ?? '—'],
                ['Registered', date('F j, Y, g:i A')],
              ])
            . self::btn('View Investor Profile', $pUrl . '/admin/users/' . ($user['id'] ?? ''));
        return self::adminNotify("New Registration — {$name}", self::adminWrap($content, 'New investor registered'));
    }

    public static function notifyAdminKycSubmission(array $user, string $ref): bool {
        $pUrl = platform_setting('platform_website','https://nexvest.com');
        $name = trim($user['first_name'] . ' ' . $user['last_name']);
        $content = self::eyebrow('Identity verification')
            . self::heading('KYC documents received.')
            . self::body("An investor has submitted identity documents and is awaiting your review.")
            . self::dataTable([
                ['Investor',  $name],
                ['Email',     $user['email']],
                ['Reference', $ref],
                ['Submitted', date('F j, Y, g:i A')],
              ])
            . self::alert('warning', 'Review the submitted documents promptly to maintain compliance standards.')
            . self::btn('Review KYC submission', $pUrl . '/admin/kyc');
        return self::adminNotify("KYC Submission — {$name} ({$ref})", self::adminWrap($content, 'Action required — KYC awaiting review'));
    }

    public static function notifyAdminDeposit(array $user, array $invoice): bool {
        $pUrl   = platform_setting('platform_website','https://nexvest.com');
        $sym    = platform_setting('platform_symbol', '$');
        $name   = trim($user['first_name'] . ' ' . $user['last_name']);
        $fmtAmt = $sym . number_format((float)$invoice['amount'], 2);
        $content = self::eyebrow('Deposit')
            . self::heading('New deposit submitted.')
            . self::body("An investor has submitted proof of payment and is awaiting deposit confirmation.")
            . self::amountCard($fmtAmt, 'Amount submitted', $invoice['reference'] ?? '—')
            . self::dataTable([
                ['Investor', $name],
                ['Email',    $user['email']],
                ['Method',   ucfirst($invoice['method'] ?? '—')],
                ['Date',     date('F j, Y, g:i A')],
              ])
            . self::alert('info', 'Verify the payment and approve or reject from the deposits panel.')
            . self::btn('Review deposit', $pUrl . '/admin/deposits');
        return self::adminNotify("Deposit — {$fmtAmt} from {$name}", self::adminWrap($content, 'Action required — deposit awaiting confirmation'));
    }

    public static function notifyAdminWithdrawal(array $user, array $wr): bool {
        $pUrl   = platform_setting('platform_website','https://nexvest.com');
        $sym    = platform_setting('platform_symbol', '$');
        $name   = trim($user['first_name'] . ' ' . $user['last_name']);
        $fmtAmt = $sym . number_format((float)$wr['amount'], 2);
        $content = self::eyebrow('Withdrawal')
            . self::heading('Withdrawal request received.')
            . self::body("An investor has requested a withdrawal. Please review and approve or reject from the admin panel.")
            . self::amountCard($fmtAmt, 'Requested amount', $wr['reference'] ?? '—')
            . self::dataTable([
                ['Investor', $name],
                ['Email',    $user['email']],
                ['Method',   ucfirst($wr['method'] ?? '—')],
                ['Date',     date('F j, Y, g:i A')],
              ])
            . self::alert('warning', '<strong>Before approving:</strong> confirm KYC status and verify destination account details match records on file.')
            . self::btn('Review withdrawal', $pUrl . '/admin/withdrawals');
        return self::adminNotify("Withdrawal — {$fmtAmt} from {$name}", self::adminWrap($content, 'Action required — withdrawal pending review'));
    }

    public static function notifyAdminTicket(array $user, array $ticket): bool {
        $pUrl    = platform_setting('platform_website','https://nexvest.com');
        $name    = trim($user['first_name'] . ' ' . $user['last_name']);
        $content = self::eyebrow('Support')
            . self::heading('New support ticket opened.')
            . self::body("An investor has opened a support ticket and is awaiting a response.")
            . self::dataTable([
                ['Investor',   $name],
                ['Email',      $user['email']],
                ['Ticket ref', $ticket['reference'] ?? '—'],
                ['Subject',    $ticket['subject'] ?? '—'],
                ['Priority',   ucfirst($ticket['priority'] ?? 'normal')],
                ['Opened',     date('F j, Y, g:i A')],
              ])
            . self::alert('info', 'Respond promptly to maintain quality support standards.')
            . self::btn('View ticket', $pUrl . '/admin/tickets/' . ($ticket['id'] ?? ''));
        return self::adminNotify("New Ticket — " . ($ticket['subject'] ?? 'Support Request') . " ({$name})", self::adminWrap($content, 'New support ticket opened'));
    }

    // ═════════════════════════════════════════════════════════
    //  MARKETING / COLD-OUTREACH  (to potential clients)
    //  Separate, un-branded-transactional template: classy,
    //  restrained, with an optional Featured Opportunity card
    //  that mirrors the investor dashboard.
    // ═════════════════════════════════════════════════════════

    /** Deterministic per-email unsubscribe token (no DB row needed at send time). */
    public static function unsubToken(string $email): string {
        $secret = (string) (CONFIG['app']['key'] ?? '');
        if ($secret === '') $secret = 'nexvest::' . platform_setting('platform_name', 'NexVest');
        return substr(hash_hmac('sha256', strtolower(trim($email)), $secret), 0, 32);
    }

    /** Public marketing site = the apex domain of the configured website (equity.foo.com -> foo.com). */
    private static function marketingSiteUrl(): string {
        $url    = platform_setting('platform_website', 'https://nexvest.com');
        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'https';
        $host   = parse_url($url, PHP_URL_HOST) ?: preg_replace('#^https?://#', '', rtrim($url, '/'));
        $labels = explode('.', $host);
        if (count($labels) > 2) $host = implode('.', array_slice($labels, -2)); // drop leading subdomain(s)
        return $scheme . '://' . $host;
    }

    public static function unsubUrl(string $email): string {
        $url = rtrim(platform_setting('platform_website', 'https://nexvest.com'), '/');
        return $url . '/unsubscribe?e=' . urlencode($email) . '&t=' . self::unsubToken($email);
    }

    /**
     * One featured-opportunity card. Deliberately LIGHT-themed (no large dark
     * panel): mail-client dark mode inverts big dark backgrounds badly (it keeps
     * the dark fill but darkens the white text on it → invisible). A light card
     * with dark text inverts gracefully and stays readable in every client.
     */
    private static function featuredCard(array $inv): string {
        $appUrl = rtrim(platform_setting('platform_website', 'https://nexvest.com'), '/');
        $isRE   = ($inv['type'] ?? '') === 'real_estate';
        $navy   = '#1E3A5F';
        $typeLbl= $isRE ? 'Real Estate' : 'Index Fund';
        $roi    = htmlspecialchars((string) ($inv['roi'] ?? '0'));
        $min    = fmt_currency((float) ($inv['min_investment'] ?? 0));
        $dur    = (int) ($inv['duration_value'] ?? 0) . ' ' . ucfirst((string) ($inv['duration_unit'] ?? 'months'));
        $thirdV = $isRE
            ? ucwords(str_replace('_', ' ', (string) ($inv['payout_frequency'] ?? 'monthly')))
            : ucwords(str_replace('_', ' ', (string) ($inv['risk_level'] ?? 'medium')));
        $thirdL = $isRE ? 'Payout' : 'Risk';
        $name   = htmlspecialchars((string) ($inv['name'] ?? ''));
        $loc    = $isRE
            ? htmlspecialchars(trim(implode(', ', array_filter([$inv['city'] ?? '', $inv['country'] ?? '']))))
            : htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($inv['risk_level'] ?? 'medium'))) . ' risk');
        $link   = $appUrl . '/investor/investments/' . (int) ($inv['id'] ?? 0);

        // Optional property photo band on top (images are never recoloured by dark mode).
        $imgBand = '';
        if (!empty($inv['image'])) {
            $src = htmlspecialchars(file_url_abs((string) $inv['image']), ENT_QUOTES);
            $imgBand = "<tr><td style='padding:0'><img src='{$src}' width='100%' alt='{$name}' style='display:block;width:100%;max-height:150px;object-fit:cover'/></td></tr>";
        }

        // Min / Duration / Payout|Risk — dark values on white
        $stat = fn(string $l, string $v) =>
            "<td width='33.33%' valign='top' style='padding:0'>
               <div class='m-sub' style='font-size:9.5px;letter-spacing:.06em;text-transform:uppercase;color:#9AA0AC;margin-bottom:3px'>{$l}</div>
               <div class='m-ink' style='font-size:15px;font-weight:700;color:#14213A'>{$v}</div>
             </td>";

        // funding progress
        $fund = '';
        $target = (float) ($inv['funding_target'] ?? 0);
        if ($target > 0) {
            $raised = (float) ($inv['funding_raised'] ?? 0);
            $pct    = min(100, (int) round($raised / $target * 100));
            $fund = "<table width='100%' cellpadding='0' cellspacing='0' style='margin:2px 0 9px'>
                <tr>
                  <td class='m-sub' style='font-size:11.5px;color:#6B7280'><b class='m-ink' style='color:#1F2937'>{$pct}% Funded</b></td>
                  <td align='right' class='m-sub' style='font-size:11.5px;color:#6B7280'>" . fmt_currency($raised) . " of " . fmt_currency($target) . "</td>
                </tr>
              </table>
              <div style='height:6px;background:#EEF0F3;border-radius:99px;overflow:hidden;margin:0 0 22px'>
                <div style='height:6px;width:{$pct}%;background:#0E9F6E;border-radius:99px'></div>
              </div>";
        }

        return "<table width='100%' cellpadding='0' cellspacing='0' bgcolor='#ffffff' style='background:#ffffff;border:1px solid #E7E9EE;border-radius:16px;overflow:hidden;margin:14px 0;box-shadow:0 2px 6px rgba(16,24,40,.06)'>
          {$imgBand}
          <!-- header row: big ROI (navy) + type pill -->
          <tr><td class='m-white' bgcolor='#ffffff' style='background:#ffffff;padding:18px 20px 0'>
            <table width='100%' cellpadding='0' cellspacing='0'>
              <tr>
                <td valign='top'>
                  <div class='m-ink' style='font-size:32px;font-weight:700;color:{$navy};letter-spacing:-.5px;line-height:1'>{$roi}%</div>
                  <div class='m-sub' style='font-size:9.5px;letter-spacing:.06em;text-transform:uppercase;color:#9AA0AC;margin-top:4px'>Total ROI</div>
                </td>
                <td align='right' valign='top'>
                  <span class='m-ink' style='display:inline-block;background:#EAF0F7;color:{$navy};font-size:10.5px;font-weight:700;padding:6px 13px;border-radius:99px'>{$typeLbl}</span>
                </td>
              </tr>
            </table>
            <table width='100%' cellpadding='0' cellspacing='0' style='margin-top:18px'>
              <tr>" . $stat('Min.', $min) . $stat('Duration', $dur) . $stat($thirdL, $thirdV) . "</tr>
            </table>
            <div style='height:1px;background:#EEF0F3;margin:18px 0 0'></div>
          </td></tr>
          <!-- body: name, location, funding, CTA -->
          <tr><td class='m-white' bgcolor='#ffffff' style='background:#ffffff;padding:15px 20px 20px'>
            <div class='m-ink' style='font-size:17px;font-weight:600;color:#111827;letter-spacing:-.2px;line-height:1.3'>{$name}</div>
            " . ($loc ? "<div class='m-sub' style='font-size:12.5px;color:#9AA0AC;margin:4px 0 14px'>{$loc}</div>" : "<div style='height:14px'></div>") . "
            {$fund}
            <a href='{$link}' class='m-btn' style='display:block;text-align:center;background:{$navy};color:#ffffff;font-size:13.5px;font-weight:600;text-decoration:none;padding:13px 20px;border-radius:9px'>View this opportunity &rarr;</a>
          </td></tr>
        </table>";
    }

    /** Classy marketing wrapper (distinct from transactional wrap) with unsubscribe footer. */
    private static function marketingWrap(string $content, string $preheader, string $unsubUrl): string {
        $pName   = platform_setting('platform_name',    'NexVest');
        $pInit   = platform_setting('platform_initials','NV');
        $pUrl    = rtrim(platform_setting('platform_website', 'https://nexvest.com'), '/');
        $pAddr   = platform_setting('platform_address', '');
        $pEmail  = platform_setting('platform_email',   'noreply@nexvest.com');
        $legalCo = platform_setting('legal_company_name', $pName . ' — ' . platform_setting('platform_tagline','Capital Group'));

        // Header brand: a large uploaded logo stands alone; otherwise fall back to the initials badge + name.
        $logo = trim((string) platform_setting('platform_logo', ''));
        if ($logo !== '') {
            $src = htmlspecialchars(file_url_abs($logo), ENT_QUOTES);
            $alt = htmlspecialchars($pName, ENT_QUOTES);
            $headerBrand = "<img src='{$src}' alt='{$alt}' height='52' style='display:inline-block;height:52px;width:auto;max-width:240px'/>";
        } else {
            $badge = "<span style='display:inline-block;background:#14161C;border-radius:8px;width:44px;height:44px;text-align:center;line-height:44px;font-size:14px;font-weight:700;color:#ffffff'>" . htmlspecialchars($pInit) . "</span>";
            $headerBrand = "<table cellpadding='0' cellspacing='0' align='center'><tr><td>{$badge}</td>"
                . "<td class='m-ink' style='padding-left:11px;font-size:16px;font-weight:600;color:#14161C;vertical-align:middle'>{$pName}</td></tr></table>";
        }

        $pre = $preheader
            ? "<div style='display:none;max-height:0;overflow:hidden;font-size:1px;color:#F4F5F7'>{$preheader}&nbsp;&zwnj;</div>"
            : '';
        $addrLine = $pAddr ? htmlspecialchars($pAddr) . '<br/>' : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<meta name="color-scheme" content="light dark"/>
<meta name="supported-color-schemes" content="light dark"/>
<title>{$pName}</title>
<style>
:root{color-scheme:light dark;supported-color-schemes:light dark}
body,html{margin:0;padding:0;background:#ffffff;-webkit-font-smoothing:antialiased}
table{border-spacing:0;border-collapse:collapse}td{padding:0}a{text-decoration:none}img{border:0;display:block}
@media only screen and (max-width:600px){.pad{padding-left:22px!important;padding-right:22px!important}}
/* Hold the intended look under forced/auto dark mode (iOS Mail, Gmail, Outlook) */
@media (prefers-color-scheme: dark){
  .m-canvas{background:#ffffff!important}
  .m-white{background:#ffffff!important}
  .m-ink{color:#111827!important}
  .m-sub{color:#6B7280!important}
  .m-mut{color:#8B909C!important}
  .m-onhero,.m-heroval{color:#ffffff!important}
  .m-btn{background:#1E3A5F!important;color:#ffffff!important}
  .m-btn2{background:#1F3A5F!important;color:#ffffff!important}
}
[data-ogsc] .m-white{background:#ffffff!important}
[data-ogsc] .m-ink{color:#111827!important}
[data-ogsc] .m-sub{color:#6B7280!important}
[data-ogsc] .m-mut{color:#8B909C!important}
[data-ogsc] .m-onhero,[data-ogsc] .m-heroval{color:#ffffff!important}
[data-ogsc] .m-btn{background:#1E3A5F!important;color:#ffffff!important}
[data-ogsc] .m-btn2{background:#1F3A5F!important;color:#ffffff!important}
</style></head>
<body class="m-canvas" style="margin:0;padding:0;background:#ffffff;font-family:-apple-system,'Segoe UI',Helvetica,Arial,sans-serif">
{$pre}
<table width="100%" cellpadding="0" cellspacing="0" class="m-canvas" style="background:#ffffff">
<tr><td align="center" class="outer" style="padding:0">
<table width="640" cellpadding="0" cellspacing="0" class="m-white" bgcolor="#ffffff" style="max-width:640px;width:100%;background:#ffffff">

  <!-- header -->
  <tr><td class="pad m-white" align="center" bgcolor="#ffffff" style="padding:24px 40px;border-bottom:1px solid #EDEFF3;background:#ffffff;text-align:center">
    {$headerBrand}
  </td></tr>

  <!-- body -->
  <tr><td class="pad m-white" bgcolor="#ffffff" style="padding:44px 40px 8px;background:#ffffff">{$content}</td></tr>

  <!-- footer -->
  <tr><td class="pad m-white" bgcolor="#ffffff" style="padding:34px 40px 36px;border-top:1px solid #EDEFF3;background:#ffffff">
    <div style="text-align:center">
      <a href="{$pUrl}" class="m-sub" style="font-size:12.5px;color:#4B5563;margin:0 12px">Website</a>
      <a href="{$pUrl}/investor/how-it-works" class="m-sub" style="font-size:12.5px;color:#4B5563;margin:0 12px">How it works</a>
      <a href="{$pUrl}/support" class="m-sub" style="font-size:12.5px;color:#4B5563;margin:0 12px">Contact</a>
    </div>
    <div class="m-mut" style="text-align:center;font-size:11.5px;line-height:1.7;color:#8B909C;margin-top:18px">
      <b class="m-sub" style="color:#4B5563;font-weight:600">{$legalCo}</b><br/>
      {$addrLine}
      You received this email because you expressed interest in investment opportunities.<br/>
      <a href="{$unsubUrl}" class="m-sub" style="color:#4B5563;text-decoration:underline">Unsubscribe</a>
    </div>
    <div class="m-mut" style="text-align:center;font-size:10.5px;line-height:1.6;color:#AEB2BD;margin-top:14px">
      Investing carries risk, including loss of capital. Stated returns are targets under each product's terms and are
      not guaranteed. Past performance does not indicate future results. This message is not investment advice or an
      offer where unlawful.
    </div>
  </td></tr>

</table>
</td></tr></table>
</body></html>
HTML;
    }

    /**
     * Send a marketing email to a potential client.
     * @param string $email    recipient
     * @param string $subject  admin-written subject
     * @param string $body     admin-written message (plain text, line breaks preserved)
     * @param string $headline optional headline shown above the body
     * @param array  $invs      zero or more investments rows — each renders a dashboard-style card
     * @param string $ctaLabel optional CTA button label (link is always the known registration path)
     */
    public static function sendMarketing(string $email, string $subject, string $body, string $headline = '', array $invs = [], string $ctaLabel = ''): bool {
        $pUrl = rtrim(platform_setting('platform_website', 'https://nexvest.com'), '/');

        $content = '';
        if (trim($headline) !== '') {
            $content .= "<div class='m-ink' style='font-size:27px;font-weight:600;color:#14161C;letter-spacing:-.5px;line-height:1.25;margin-bottom:18px;text-align:center'>"
                      . htmlspecialchars($headline) . "</div>";
        }
        $content .= "<div class='m-sub' style='font-size:16px;line-height:1.75;color:#4B5563'>" . nl2br(htmlspecialchars($body)) . "</div>";

        if ($invs) {
            $content .= "<div class='m-mut' style='font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#9CA3AF;margin:30px 0 4px'>"
                      . (count($invs) > 1 ? 'Featured opportunities' : 'Featured opportunity') . "</div>";
            foreach ($invs as $inv) $content .= self::featuredCard($inv);
        }

        // Link is always a path we already own — the admin never types a URL.
        $label = trim($ctaLabel) !== '' ? htmlspecialchars($ctaLabel) : 'Visit website';
        $site  = self::marketingSiteUrl();
        $content .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin:34px 0 6px'><tr><td align='center'>"
                  . "<a href='{$site}' class='m-btn2' style='display:inline-block;background:#1F3A5F;color:#ffffff;font-size:14.5px;font-weight:600;text-decoration:none;padding:14px 34px;border-radius:8px'>{$label} &rarr;</a>"
                  . "</td></tr></table>";

        $pre = trim($headline) !== '' ? $headline : mb_substr(trim($body), 0, 90);
        return self::send($email, '', $subject, self::marketingWrap($content, $pre, self::unsubUrl($email)));
    }
}
