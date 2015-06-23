
window.onload = function(){
	checkb = document.getElementById("checker");
	checkb.addEventListener("click", changeType);
	function changeType() {
		var pass = document.getElementById("pwd");
		
		if(checkb.checked) {
			pass.setAttribute("type", "text");
		}
		else {
			pass.setAttribute("type", "password");
		}
	}

	submi = document.getElementById("submi");
	submi.addEventListener("mousedown", changeTypeForSending);
	function changeTypeForSending() {
		var pass = document.getElementById("pwd");
		pass.setAttribute("type", "password");
	}
}