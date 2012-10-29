/**
* Home.js by @warhawk3407
*
* http://www.gnu.org/copyleft/gpl.html
*/



// <-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -->

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

// <-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -->



$(document).ready(function() {

// <-- -- -- -- -- -- -- -- -- -- -- -->

	$(".collapse").collapse();

	// <-- -- -- -- -- --> 1

	$('#collapseOne').on('hidden', function () {
		createCookie('collapseOneHidden','yes',24);

	})
	$('#collapseOne').on('show', function () {
		eraseCookie('collapseOneHidden');
	})

	var x = readCookie('collapseOneHidden')
	if (x == 'yes') {
		$('#collapseOne').collapse('hide')
	}

	// <-- -- -- -- -- --> 2

	$('#collapseTwo').on('hidden', function () {
		createCookie('collapseTwoHidden','yes',24);

	})
	$('#collapseTwo').on('show', function () {
		eraseCookie('collapseTwoHidden');
	})

	x = readCookie('collapseTwoHidden')
	if (x == 'yes') {
		$('#collapseTwo').collapse('hide')
	}

	// <-- -- -- -- -- --> 3

	$('#collapseThree').on('hidden', function () {
		createCookie('collapseThreeHidden','yes',24);

	})
	$('#collapseThree').on('show', function () {
		eraseCookie('collapseThreeHidden');
	})

	x = readCookie('collapseThreeHidden')
	if (x == 'yes') {
		$('#collapseThree').collapse('hide')
	}

	// <-- -- -- -- -- --> 4

	$('#collapseFour').on('hidden', function () {
		createCookie('collapseFourHidden','yes',24);

	})
	$('#collapseFour').on('show', function () {
		eraseCookie('collapseFourHidden');
	})

	x = readCookie('collapseFourHidden')
	if (x == 'yes') {
		$('#collapseFour').collapse('hide')
	}

	// <-- -- -- -- -- --> 5

	$('#collapseFive').on('hidden', function () {
		createCookie('collapseFiveHidden','yes',24);

	})
	$('#collapseFive').on('show', function () {
		eraseCookie('collapseFiveHidden');
	})

	x = readCookie('collapseFiveHidden')
	if (x == 'yes') {
		$('#collapseFive').collapse('hide')
	}

	// <-- -- -- -- -- --> 6

	$('#collapseSix').on('hidden', function () {
		createCookie('collapseSixHidden','yes',24);

	})
	$('#collapseSix').on('show', function () {
		eraseCookie('collapseSixHidden');
	})

	x = readCookie('collapseSixHidden')
	if (x == 'yes') {
		$('#collapseSix').collapse('hide')
	}

	// <-- -- -- -- -- --> 7

	$('#collapseSeven').on('hidden', function () {
		createCookie('collapseSevenHidden','yes',24);

	})
	$('#collapseSeven').on('show', function () {
		eraseCookie('collapseSevenHidden');
	})

	x = readCookie('collapseSevenHidden')
	if (x == 'yes') {
		$('#collapseSeven').collapse('hide')
	}

	// <-- -- -- -- -- --> 8

	$('#collapseEight').on('hidden', function () {
		createCookie('collapseEightHidden','yes',24);

	})
	$('#collapseEight').on('show', function () {
		eraseCookie('collapseEightHidden');
	})

	x = readCookie('collapseEightHidden')
	if (x == 'yes') {
		$('#collapseEight').collapse('hide')
	}

// <-- -- -- -- -- -- -- -- -- -- -- -->

});
