/**
 * Populates the Quick Edit "Status" select with the piece's current status
 * when the inline editor opens, since WordPress core doesn't do this
 * automatically for custom columns.
 */
( function ( $ ) {
	'use strict';

	const wpInlineEdit = inlineEditPost ? inlineEditPost.edit : null;
	if ( ! wpInlineEdit ) {
		return;
	}

	inlineEditPost.edit = function ( id ) {
		wpInlineEdit.apply( this, arguments );

		const postId = typeof id === 'object' ? inlineEditPost.getId( id ) : id;
		if ( ! postId ) {
			return;
		}

		const row = document.getElementById( 'post-' + postId );
		const badge = row ? row.querySelector( '.verena-status-badge' ) : null;
		const status = badge ? badge.getAttribute( 'data-status' ) : 'available';

		const editRow = document.getElementById( 'edit-' + postId );
		const select = editRow ? editRow.querySelector( 'select.verena-quick-edit-status' ) : null;
		if ( select ) {
			select.value = status;
		}
	};
} )( jQuery );
