<?php
// ============================================================
//  NexVest — Public Controller
//  app/controllers/PublicController.php
// ============================================================

declare(strict_types=1);

class PublicController {

    // ── Certificate Verification (public page) ────────────────
    public static function verifyCertificate(): void {
        AuthMiddleware::init();
        $ref     = sanitize($_GET['ref'] ?? '');
        $holding = null;
        $user    = null;
        $inv     = null;

        if ($ref) {
            $holding = DB::fetch(
                "SELECT ih.*, i.name AS inv_name, i.type, i.roi AS inv_roi,
                        i.duration_value, i.duration_unit, i.city, i.country AS inv_country
                 FROM investment_holdings ih
                 JOIN investments i ON i.id = ih.investment_id
                 WHERE ih.certificate_ref = ? AND ih.status IN ('active','matured')",
                [$ref]
            );
            if ($holding) {
                $user = DB::fetch(
                    "SELECT first_name, last_name, country FROM users WHERE id=?",
                    [$holding['user_id']]
                );
            }
        }

        view('public.verify_certificate', compact('ref','holding','user'), 'minimal');
    }
}
