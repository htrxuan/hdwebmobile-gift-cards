<?php

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Only file in the plugin that touches $wpdb directly. Every query is parameterized,
 * including the table name via the %i identifier placeholder (WP 6.2+).
 *
 * Direct queries against a custom table are unavoidable here -- there is no WP API
 * for this data -- so DirectDatabaseQuery/NoCaching advisories are expected and accepted
 * for this class, matching standard practice for custom-table plugins.
 */
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
class HDGC_Repository
{

    public static function get_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'hdgc_gift_cards';
    }

    public static function get_schema_sql()
    {
        global $wpdb;
        $table           = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            code VARCHAR(32) NOT NULL,
            initial_amount DECIMAL(19,4) NOT NULL,
            balance DECIMAL(19,4) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            order_id BIGINT UNSIGNED DEFAULT NULL,
            purchaser_email VARCHAR(200) DEFAULT NULL,
            expires_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            KEY purchaser_email (purchaser_email),
            KEY status (status)
        ) {$charset_collate};";
    }

    /**
     * Cryptographically random, dash-grouped code -- never derived from or influenced
     * by user input, closing off the guessable/enumerable-code vulnerability class.
     */
    public static function generate_unique_code()
    {
        do {
            $raw  = strtoupper(bin2hex(random_bytes(8)));
            $code = implode('-', str_split($raw, 4));
        } while (self::find_by_code($code));

        return $code;
    }

    public static function create($amount, $currency, $order_id = null, $purchaser_email = '', $expires_at = null)
    {
        global $wpdb;

        $code = self::generate_unique_code();

        $wpdb->insert(
            self::get_table_name(),
            array(
                'code'            => $code,
                'initial_amount'  => $amount,
                'balance'         => $amount,
                'currency'        => $currency,
                'status'          => 'active',
                'order_id'        => $order_id,
                'purchaser_email' => $purchaser_email,
                'expires_at'      => $expires_at,
                'created_at'      => current_time('mysql'),
            ),
            array('%s', '%f', '%f', '%s', '%s', '%d', '%s', '%s', '%s')
        );

        return self::find_by_code($code);
    }

    public static function find_by_code($code)
    {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            'SELECT * FROM %i WHERE code = %s',
            self::get_table_name(),
            $code
        ));
    }

    public static function find_for_account($email)
    {
        global $wpdb;

        if (empty($email)) {
            return array();
        }

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM %i WHERE purchaser_email = %s AND status = 'active' AND balance > 0 ORDER BY created_at DESC",
            self::get_table_name(),
            $email
        ));
    }

    public static function adjust_balance($id, $delta)
    {
        global $wpdb;

        // Clamp at the DB layer too (not just in calling code) so a balance can never go
        // negative regardless of caller math -- GREATEST(balance + delta, 0).
        $wpdb->query($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            'UPDATE %i SET balance = GREATEST(balance + %f, 0) WHERE id = %d',
            self::get_table_name(),
            $delta,
            $id
        ));
    }

    public static function set_status($id, $status)
    {
        global $wpdb;

        $wpdb->update(
            self::get_table_name(),
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
    }

    /**
     * @param array $args { status, s (code/email search), per_page, paged, orderby, order }
     * @return array { items: array, total: int }
     */
    public static function get_for_list_table(array $args)
    {
        global $wpdb;

        $where  = array('1=1');
        $params = array();

        if (!empty($args['status']) && 'all' !== $args['status']) {
            $where[]  = 'status = %s';
            $params[] = $args['status'];
        }

        if (!empty($args['s'])) {
            $where[]  = '(code LIKE %s OR purchaser_email LIKE %s)';
            $like     = '%' . $wpdb->esc_like($args['s']) . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $where_sql = implode(' AND ', $where);

        $allowed_orderby = array('created_at', 'balance', 'status', 'purchaser_email', 'expires_at');
        $orderby         = in_array($args['orderby'] ?? '', $allowed_orderby, true) ? $args['orderby'] : 'created_at';
        $order           = 'ASC' === strtoupper($args['order'] ?? '') ? 'ASC' : 'DESC';

        $per_page = max(1, (int) ($args['per_page'] ?? 20));
        $paged    = max(1, (int) ($args['paged'] ?? 1));
        $offset   = ($paged - 1) * $per_page;

        // $where_sql/$orderby/$order are built only from the hardcoded, safe fragments and
        // allow-lists above (never raw user input); all real values still go through prepare().
        $total = (int) $wpdb->get_var($wpdb->prepare( // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            "SELECT COUNT(*) FROM %i WHERE {$where_sql}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            array_merge(array(self::get_table_name()), $params)
        ));

        $items = $wpdb->get_results($wpdb->prepare( // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
            "SELECT * FROM %i WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            array_merge(array(self::get_table_name()), $params, array($per_page, $offset))
        ));

        return array(
            'items' => $items,
            'total' => $total,
        );
    }
}
