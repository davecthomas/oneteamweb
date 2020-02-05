// Toggle visibility of a document element
function togglevis (id) {
	var element = document.getElementById(id);
	// Try to get the image for toggling any anchor image associated 
	// Using the convention "<div element name>_img" for the image
	var img_element = document.getElementById(id + "_img")
	var toggle_image = false;
	if (img_element) {
		toggle_image = true;
	}
	if (element.className=="showit") { 
		element.className="hideit"; 
		if (toggle_image) {
			img_element.src="/1team/img/a_expand.gif";
		}
	} 
	else { 
		element.className="showit"; 
		if (toggle_image) {
			img_element.src="/1team/img/a_collapse.gif";
		}
	}
} 

function togglerender (div_id, element, page) {
	// Show the div
	togglevis(div_id);
	// Render the page in the iframe inside the div
	frames[element].location.href=page;
} 


// Validate passwords match
function validate(form) {
   var e = form.elements;

   if(e['new-password'].value != e['new-password-confirm'].value) {
	  alert('Your new passwords do not match. Try again.');
	  return false;
   }
   return true;
}


