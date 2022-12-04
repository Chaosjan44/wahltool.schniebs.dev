var str = "?";
refresh();
startTimer();
var timer;
function startTimer() {
    timer = setInterval(
        function() {
            refresh()
        }, 
        2500); // 2.5 seconds
}
function refresh() {
    str = str;
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        if (str != "?stop") {
            document.getElementById("poll_div").innerHTML = this.responseText;
            str = "?stop";
            // const queryString = window.location.search;
            // console.log(queryString);
            // const urlParams = new URLSearchParams(queryString);
            // const error_votes = urlParams.get('error_votes')
            // console.log(error_votes);
            // if (error_votes == 1) {
            //     msg = "Du hast die falsche Anzahl an Stimmen abgegeben!"
            //     document.getElementById('errormsg').innerHTML = msg;
            // }
        } 
        if (this.responseText.includes('<div style="display: none">stop</div>')) {
            str = "?stop";
        } else if (this.responseText.includes('<div style="display: none">unstop</div>')) {
            str = "";
        }
    }
    xhttp.open("GET", "/php/refresher.php");
    xhttp.send();
}
