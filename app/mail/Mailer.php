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
}
