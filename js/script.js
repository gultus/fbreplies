var timeouts = new Array();

function handle(){
	var div = document.getElementById(this.id.replace("-content",""));
	var chkBox = div.getElementsByTagName("input")[0];
	if(chkBox.checked){
		chkBox.checked = false;
		div.className = div.className.replace(/ .*$/," deselected");
	}else{
		chkBox.checked = true;
		div.className = div.className.replace(/ .*$/," selected");
	}
}

function move(a,b){
	var from = document.getElementById(a);
	var to = document.getElementById(b);
	var inputs = from.getElementsByTagName("input");
	var count = 0;
	for(i=0;i<inputs.length;i++){
		if(inputs[i].type == "checkbox" && inputs[i].checked){
			var id = inputs[i].value;
			inputs[i].checked = false;
			var child = document.getElementById(id);
			var oldChild = from.removeChild(child);
			oldChild.className = oldChild.className.replace(/ .*$/," deselected");
			to.appendChild(oldChild);
			count++;
			i--;
			var link = document.getElementById(id+"-toggle-link");
			var msgboxcontainer = document.getElementById(id+"-custom-msg-box-container");
			var msgbox = document.getElementById(id+"-custom-msg-box");
			if(b == "selected"){
				link.className="showbox";
			}else{
				link.className="hidebox";
			}
			msgboxcontainer.className="hidebox";
			msgbox.value = "";
		}
	}
	cleartimeouts();
	document.getElementById("available-msg").innerHTML="";
	document.getElementById("selected-msg").innerHTML="";
	document.getElementById("unwanted-msg").innerHTML="";
	var displayBox = document.getElementById(a+"-msg");
	if(count==0){
		displayBox.innerHTML = "No posts selected to move";
	}else if(count==1){
		displayBox.innerHTML = count+" post moved to "+b+" tab";
	}else{
		displayBox.innerHTML = count+" posts moved to "+b+" tab";
	}
	timeouts[timeouts.length]= window.setTimeout(function(){document.getElementById(a+"-msg").innerHTML=""},5000);
}

function cleartimeouts(){
	if (timeouts) for (var i in timeouts) if (timeouts[i]) window.clearTimeout(timeouts[i]);
	timeouts = [];
}	

function matchPosts(a){
	var pattern = ".*"+document.getElementById(a+"-regex").value+".*";
	var regex = new RegExp(pattern,"im");
	var pattern1 = ".*"+document.getElementById(a+"-regex-1").value+".*";
	var regex1 = new RegExp(pattern1,"im");
	var sel = document.getElementById(a+"-andor")
	var andor = sel.options[sel.selectedIndex].value;
	var from = document.getElementById(a);
	var inputs = from.getElementsByTagName("input");
	var count = 0;
	for(i=0;i<inputs.length;i++){
		if(inputs[i].type == "checkbox" && inputs[i].getAttribute("name") == "postid"){
			var div = document.getElementById(inputs[i].value);
			var msg = document.getElementById(inputs[i].value+"-msg").innerHTML;
			if(andor == "and"){
				var result = regex.test(msg) && regex1.test(msg);
			}else{
				var result = regex.test(msg) || regex1.test(msg);
			}
			if(result){
				div.className = div.className.replace(/ .*$/," selected");
				inputs[i].checked = true;
				count++;
			}else{
				div.className = div.className.replace(/ .*$/," deselected");
				inputs[i].checked = false;
			}
		}
	}
	cleartimeouts();
	document.getElementById("available-msg").innerHTML="";
	document.getElementById("selected-msg").innerHTML="";
	document.getElementById("unwanted-msg").innerHTML="";
	var displayBox = document.getElementById(a+"-msg");
	if(count==0){
		displayBox.innerHTML = "No matching posts found";
	}else if(count==1){
		displayBox.innerHTML = count+" matching post found";
	}else{
		displayBox.innerHTML = count+" matching posts found";
	}
	timeouts[timeouts.length]= window.setTimeout(function(){document.getElementById(a+"-msg").innerHTML=""},5000);
}

function toggleCustomMessage(id){
	var div = document.getElementById(id);
	var link = document.getElementById(id+"-msg-link"); 
	var msgboxcontainer = document.getElementById(id+"-custom-msg-box-container"); 
	var msgbox = document.getElementById(id+"-custom-msg-box"); 
	if(link.innerHTML == "Add Custom Message"){
		msgbox.value = document.getElementById("default-msg").value;
		msgboxcontainer.className = "showbox";
		link.innerHTML = "Remove Custom Message";
	}else{
		msgbox.value = "";
		msgboxcontainer.className = "hidebox";
		link.innerHTML = "Add Custom Message";
	}	
}

function toggle(linkid){
	var link = document.getElementById(linkid);
	if(link.innerHTML == "Add Custom Message"){
		link.innerHTML = "Remove Custom Message";
	}else{
		link.innerHTML = "Add Custom Message";
	}
}
