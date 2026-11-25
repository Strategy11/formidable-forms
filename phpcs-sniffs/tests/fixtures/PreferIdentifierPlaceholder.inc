<?php
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}frm_item_metas WHERE field_id=%d AND item_id=%d", $field_id, $entry_id ) );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_item_metas WHERE field_id=%d', $id ) );
$ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type in (%s, %s)', $a, $b ) );
$result = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $wpdb->prefix . $table . ' LIKE %s', $column ) );
$wpdb->query( $wpdb->prepare( 'DELETE fi FROM ' . $wpdb->prefix . 'frm_fields AS fi LEFT JOIN ' . $wpdb->prefix . 'frm_forms fr ON (fi.form_id = fr.id) WHERE fi.form_id=%d OR parent_form_id=%d', $id, $id ) );
$query = $wpdb->prepare( "SELECT DISTINCT item_id FROM {$wpdb->prefix}frm_item_metas WHERE meta_value {$operator} %s and field_id = %d", $search, $field_id );
$sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}frm_subscriptions` WHERE id = %d", $id ) );
$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}frm_payments WHERE completed is NOT NULL" ) );
$results = $wpdb->get_results( $wpdb->prepare( "SELECT p.* FROM `{$wpdb->prefix}frm_{$table_name}` p LEFT JOIN `{$wpdb->prefix}frm_items` e ON p.item_id = e.id WHERE e.id = %d", $id ) );
$wpdb->query( $wpdb->prepare( "CREATE INDEX idx_is_draft_created_at ON {$wpdb->prefix}frm_items (is_draft, created_at)" ) );
$result = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT COUNT(*) as items_count
			FROM {$wpdb->prefix}frm_items AS it INNER JOIN {$wpdb->prefix}frm_forms AS fr ON it.form_id = fr.id
			WHERE it.created_at BETWEEN %s AND %s",
		$from_date,
		$to_date
	)
);
$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->fields );
$wpdb->query( "CREATE INDEX idx_form_id_type ON `{$wpdb->prefix}frm_fields` (form_id, type(30))" );
$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $this->table_name );
$late = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'frm_items WHERE name = %s AND form_id IN ( SELECT id FROM ' . $wpdb->prefix . 'frm_forms WHERE status = %s )', $name, $status ) );
// A second dynamic SQL fragment makes the argument order unknowable.
$wpdb->query( $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'frm_forms SET status = %s ' . $where['where'], $where['values'] ) );
$sql = $wpdb->prepare( ' LEFT JOIN ' . $wpdb->prefix . 'frm_item_metas em' . $o_key . ' ON em' . $o_key . '.field_id=%d ', $o_field->id );
// prepare() only unpacks an array when it is the sole argument.
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_items WHERE id = %d', array( $id ) ) );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_items WHERE id IN (%d, %d)', $ids ) );
// Numbered placeholders change the argument mapping.
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_items WHERE id = %1$d OR parent_item_id = %1$d', $id ) );
// A percent sign or an interpolated value would be read as a placeholder once wrapped in prepare().
$found = $wpdb->get_var( "SELECT 1 FROM {$wpdb->posts} WHERE post_content LIKE '%[formidable %' LIMIT 1" );
$match = $wpdb->get_var( "SELECT 1 FROM {$wpdb->posts} WHERE post_title = '{$title}'" );
$other = $wpdb->get_var( 'SELECT 1 FROM ' . $wpdb->posts . ' WHERE ' . $where );
// Already correct, or not a table built into the SQL.
$wpdb->query( "CREATE TABLE {$wpdb->prefix}frm_payments ( id BIGINT )" );
$wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE id = %d', $wpdb->prefix . 'frm_items', $id ) );
$wpdb->query( $wpdb->prepare( 'DELETE FROM wp_frm_items WHERE id = %d', $id ) );
$count = FrmDb::get_var( 'frm_forms', array( 'id' => $id ), 'COUNT(*)' );
$x = $wpdb->prepare( "SELECT * FROM %i em{$o_key} WHERE em{$o_key}.field_id = %d", $wpdb->prefix . 'frm_item_metas', $o_field->id );
$y = $wpdb->prepare( "SELECT * FROM %i pm{$o_key} INNER JOIN %i it ON pm{$o_key}.post_id = it.post_id WHERE it.id = %d", $wpdb->postmeta, $wpdb->prefix . 'frm_items', $id );
$wpdb->query( $sql );
$other_db->query( 'DROP TABLE IF EXISTS ' . $this->fields );
$payments = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}frm_payments WHERE completed is NOT NULL"
	)
);
$wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . "frm_items WHERE id=%d", $id );
$wpdb->prepare( "SELECT id FROM " . $wpdb->prefix . "frm_items WHERE id=%d", $id );
$wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . "frm_items" . ' WHERE id=%d', $id );
$wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . "frm_items\nWHERE id=%d", $id );
$wpdb->prepare( "SELECT id FROM `{$wpdb->prefix}frm_" . "items` WHERE id=%d", $id );
$wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'frm_items' . "_old WHERE id=%d", $id );
$wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'frm_items' . '_old WHERE id=%d', $id );
