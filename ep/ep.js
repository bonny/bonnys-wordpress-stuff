/**
 * Scripts for EP stuff
 */

/**
 * SFEEB
 */
jQuery(document).on("click", ".sfeeb_edit_add", function(event) {

	// find the id of our post
	// sfeeb_edit sfeeb_edit_add sfeeb_edit_add_post_id_9
	var t = jQuery(this);
	var classes = t.attr("class");
	var match = classes.match(/sfeeb_edit_add_post_id_([\d]+)/);
	if (match.length == 2) {
		var post_id = match[1];
		sfeeb_add_page(post_id);
	}

	event.preventDefault();
});

function sfeeb_add_page(post_id) {

	var page_title = prompt("Enter name of new page", "Untitled");
	if (page_title) {
		
		var data = {
			"action": 'sfeeb_add_page',
			"pageID": post_id,
			"type": "after",
			"page_title": page_title,
			"post_type": "page"
		};
		
		jQuery.post(window.ep.ajaxurl, data, function(response) {
			//console.log(response);
			if (response != "0") {
				document.location = response;
			}
		});
		return false;
	
	} else {
		return false;
	}
}
