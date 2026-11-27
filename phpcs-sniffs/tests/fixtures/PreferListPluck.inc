<?php
$names = array();
foreach ( $fields as $field ) {
	$names[ $field->id ] = $field->name;
}
render_list( $names );
$labels = array();
foreach ( $options as $key => $option ) {
	$labels[ $key ] = $option['label'];
}
render_list( $labels );
$ids = array();
foreach ( $this->rows as $row ) {
	$ids[] = $row->id;
}
render_list( $ids );
$values = [];
foreach ( (array) $items as $item ) {
	$values[ $item['value'] ] = $item['label'];
}
render_list( $values );

$existing[] = 'kept';
foreach ( $fields as $field ) {
	$existing[] = $field->id;
}
$generated = array();
foreach ( get_rows() as $row ) {
	$generated[] = $row->id;
}
$methods = array();
foreach ( $rows as $row ) {
	$methods[] = $row->get_id();
}
$both = array();
foreach ( $rows as $row ) {
	$both[] = $row->id;
	$count++;
}
$commented = array();
foreach ( $rows as $row ) {
	// Keep this note.
	$commented[] = $row->id;
}
$mixed = array();
foreach ( $rows as $row ) {
	$mixed[ $row['id'] ] = $row->name;
}
$falsy = array();
foreach ( $rows as $row ) {
	$falsy[ $row[0] ] = $row[1];
}
$leaked = array();
foreach ( $entries as $entry ) {
	$leaked[] = $entry->id;
}
echo esc_html( $entry->id );
$byref = array();
foreach ( $rows as &$ref ) {
	$byref[] = $ref->id;
}
$self = array();
foreach ( $self as $item ) {
	$self[] = $item->id;
}
$other = array();
foreach ( $rows as $key => $row ) {
	$other[ $index ] = $row->id;
}
$dynamic = array();
foreach ( $rows as $row ) {
	$dynamic[] = $row->$prop;
}
$object = (object) array( 'a' => (object) array( 'x' => 1 ) );
$fromObject = array();
foreach ( $object as $key => $item ) {
	$fromObject[ $key ] = $item->x;
}
$wrapped = new ArrayObject( $rows );
$fromWrapped = array();
foreach ( $wrapped as $row ) {
	$fromWrapped[] = $row->id;
}
