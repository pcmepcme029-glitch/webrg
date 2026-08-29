<?php
/**
 * MTARG License config.
 * This file sits OUTSIDE the web root (e.g. one folder above license.php),
 * so it can never be downloaded or fetched by anyone. license.php includes it
 * via require_once.
 */

// ===== Supabase credentials (server-side only) =====
$SUPABASE_URL = 'https://kgjfiburuprozfdykmvl.supabase.co';
$SUPABASE_KEY = 'sb_publishable_42OAteN-HVcp42S7KsU5dQ_BtbMIJx6';
$TABLE        = 'licenses';
$COL_KEY      = 'license_key';
$COL_ACTIVE   = 'active';
