// Toggle visibility of a document element
function hideit(id){
	var element = document.getElementById(id);
	element.className="hideit"; 	
}

function showit(id){
	var element = document.getElementById(id);
	element.className="showit"; 	
}
function changeclass(id, newclass){
	var element = document.getElementById(id);
	element.className=newclass;
}

function togglevis2 (id, showclass, hideclass) {
	var element = document.getElementById(id);
	// Try to get the image for toggling any anchor image associated 
	// Using the convention "<div element name>_img" for the image
	var img_element = document.getElementById(id + "_img")
	var toggle_image = false;
	if (img_element) {
		toggle_image = true;
	}
	if (element.className==showclass) { 
		element.className=hideclass; 
		if (toggle_image) {
			img_element.src="/1team/img/a_expand.gif";
		}
	} 
	else { 
		element.className=showclass; 
		if (toggle_image) {
			img_element.src="/1team/img/a_collapse.gif";
		}
	}
} 
function togglevis (id) {
	togglevis2( id, "showit", "hideit");
} 

function togglerender (div_id, element, page) {
	// Show the div
	togglevis(div_id);
	// Render the page in the iframe inside the div
	frames[element].location.href=page;
} 


// Validate passwords match
function confirmpasswordmatch(form) {
   var e = form.elements;

   if(e['new-password'].value != e['new-password-confirm'].value) {
	  alert('Your new passwords do not match. Try again.');
	  return false;
   }
   return true;
}

function echeck(str) {

	var at="@"
	var dot="."
	var lat=str.indexOf(at)
	var lstr=str.length
	var ldot=str.indexOf(dot)
	if (str.indexOf(at)==-1){
	   alert("Invalid E-mail ID")
	   return false
	}

	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
	   alert("Invalid E-mail ID")
	   return false
	}

	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		alert("Invalid E-mail ID")
		return false
	}

	 if (str.indexOf(at,(lat+1))!=-1){
		alert("Invalid E-mail ID")
		return false
	 }

	 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		alert("Invalid E-mail ID")
		return false
	 }

	 if (str.indexOf(dot,(lat+2))==-1){
		alert("Invalid E-mail ID")
		return false
	 }
	
	 if (str.indexOf(" ")!=-1){
		alert("Invalid E-mail ID")
		return false
	 }

	 return true					
}

// the email form element must have a name of "email"
function ValidateEmail(form){
	var e = form.elements;
	var emailElement = e['email']
	var emailID = e['email'].value
	
	if ((emailID.indexOf("bcc") > 0) || (emailID.indexOf("BCC") > 0) || (emailID.indexOf("Bcc") > 0)){
		return false;
	}
	
	if ((emailID==null)||(emailID=="")){
		alert("Please Enter your Email ID")
		emailElement.focus()
		return false
	}
	if (echeck(emailID)==false){
		emailID=""
		emailElement.focus()
		return false
	}
	return true
}

// This will fade an element, then hide over a time period
function timedfade(id, millisec){
	opacity(id,100, 0, millisec);
}

function opacity(id, opacStart, opacEnd, millisec) { 
    //speed for each frame 
    var speed = Math.round(millisec / 100); 
    var timer = 0; 

    //determine the direction for the blending, if start and end are the same nothing happens 
    if(opacStart > opacEnd) { 
        for(i = opacStart; i >= opacEnd; i--) { 
            setTimeout("changeOpac(" + i + ",'" + id + "')",(timer * speed)); 
            timer++; 
        } 
    } else if(opacStart < opacEnd) { 
        for(i = opacStart; i <= opacEnd; i++) 
            { 
            setTimeout("changeOpac(" + i + ",'" + id + "')",(timer * speed)); 
            timer++; 
        } 
    } 
} 

//change the opacity for different browsers 
function changeOpac(opacity, id) { 
    var object = document.getElementById(id).style; 
    object.opacity = (opacity / 100); 
    object.MozOpacity = (opacity / 100); 
    object.KhtmlOpacity = (opacity / 100); 
    object.filter = "alpha(opacity=" + opacity + ")"; 
	// if the opacity is 0, we should hide the element to not cause 
	// strangeness with mouse cursoring in the browser
	if (opacity == 0){
		hideit(id);
	} 
}

function setDynamicImage( imagepath, neardiv, vertoffset){
	document.dynimg.src=imagepath;
	// move the div
	imagediv = document.getElementById('dynamicimage');
	var pos = new Array();
	pos = dynamicimagePos(neardiv);
	
	imagediv.style.top = pos[1]+ 'px'; 
	imagediv.style.left = pos[0] + 'px';	
	togglevis2('dynamicimage');
}

function dynamicimagePos(dynamicimageobj){
	dynamicimagelft=dynamicimageobj.offsetLeft;
	dynamicimagetop=dynamicimageobj.offsetTop;
	while(dynamicimageobj.offsetParent!=null){
		dynamicimagepar=dynamicimageobj.offsetParent;
		dynamicimagelft+=dynamicimagepar.offsetLeft;
		dynamicimagetop+=dynamicimagepar.offsetTop;
		dynamicimageobj=dynamicimagepar;
//		alert(dynamicimagelft + " , " + dynamicimagetop);
	}
	return [dynamicimagelft,dynamicimagetop];
}

function validatePhone(fld) {
	var strPhone  = fld.value.toString();
	var stripped = strPhone.replace(/[\(\)\.\-\ ]/g, '');

	if (fld.value == "") {
	   return false;
	} else if (isNaN(parseInt(stripped))) {
	   return false;

	} else if (!(stripped.length == 10)) {
	   return false;
	}
	return true;
}