/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function showSubjectSettings(subject_id, school_id) {
    if (subject_id == 0) {
        document.getElementById("subject_settings").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("subject_settings").innerHTML = xmlhttp.responseText;
            }
        };
        xmlhttp.open("GET","timetable_subject_settings.php?school_id="+school_id+"&subject_id="+subject_id,true);
        xmlhttp.send();
    }
}