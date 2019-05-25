<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <title>Code Live Stream</title>
</head>
<body>
    <div class="bar-header">
        <h1 class="medium-title">Code Live Stream</h1>
        <h2 class="medium-subtitle">Stream anytime and anywhere!</h2>
        <div id="vid-box"></div>
        <div id="stream-info" hidden="true">
                <button class="btn btn-success done-button" input type="reset" id="end" onclick="end(),window.location.reload();">Done</button>
                <!-- <img src="img/person_dark.png" width="30" height="30"/> -->
                <!-- <span id="here-now">0</span>        -->
        </div>
        <div class="box box-style col-lg-5">
                <form name="streamForm" id="stream" action="#" onsubmit="return errWrap(stream,this);">
                    <div class="col-lg-12 input-effect">
                        <input class="effect-17" type="text" name="streamname" id="streamname" placeholder="Enter stream name">
                        <label>Enter stream name</label>
                        <span class="focus-border"></span>
                    </div>
                    <div class="streamButton">
                        <button class="btn btn-success" type="submit" value="Stream" name="stream-submit">Stream</button>
                    </div>
                </form>
                
                <form name="watchForm" id="watch" action="#" onsubmit="return errWrap(watch,this);">
                    <div class="col-lg-12 input-effect">
                        <input class="effect-17" type="text" name="number" placeholder="Enter stream to watch!">
                        <label>Enter stream to watch!</label>
                        <span class="focus-border"></span>
                    </div>
                    <div class="watchButton">
                        <button class="btn btn-success" type="submit" value="Watch">Watch Stream</button>
                    </div>
                </form>
            </div>
            <div id="logs" class="ptext text-center" style="margin-top:16px; color:white;"></div>
    </div>
</body>
</html>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://cdn.pubnub.com/pubnub.min.js"></script>
<script src="js/webrtc.js"></script>
<script src="js/rtc-controller.js"></script>

<script>
    $(window).load(function(){
		$(".col-lg-12 input").val("");
		
		$(".input-effect input").focusout(function(){
			if($(this).val() != ""){
				$(this).addClass("has-content");
			}else{
				$(this).removeClass("has-content");
			}
		})
	});
</script>

<script type="text/javascript">
    var video_out  = document.getElementById("vid-box");
    var here_now   = document.getElementById('here-now');
    var stream_info= document.getElementById('stream-info');
    var end_stream = document.getElementById('end');

    var streamName;

    function stream(form){
        streamName = form.streamname.value || Math.floor(Math.random()*100)+'';
        var phone = window.phone = PHONE({
            number        : streamName, // listen on username line else random
            publish_key   : 'pub-c-13af87ad-f46a-4974-be1f-96c72515ad27', // Your Pub Key
            subscribe_key : 'sub-c-0c087384-7197-11e9-a1d6-2a8c316da507', // Your Sub Key
            oneway        : true,
            broadcast     : true
        });

        var ctrl = window.ctrl = CONTROLLER(phone);

        ctrl.ready(function(){
            // form.streamname.style.background="#55ff5b";
            form.streamname.value = phone.number(); 
    //		form.stream_submit.hidden="true"; 
            ctrl.addLocalStream(video_out);
            ctrl.stream();
            stream_info.hidden=false;
            end_stream.hidden =false;
            addLog("Streaming to " + streamName); 
        });

        ctrl.receive(function(session){
            session.connected(function(session){ addLog(session.number + " has joined."); });
            session.ended(function(session) { addLog(session.number + " has left."); 
            console.log(session)});
        });

        ctrl.streamPresence(function(m){
            here_now.innerHTML=m.occupancy;
            addLog(m.occupancy + " currently watching.");
        });
        return false;
    }

    function watch(form){
	    var num = form.number.value;
        var phone = window.phone = PHONE({
            number        : "Viewer" + Math.floor(Math.random()*100), // listen on username line else random
            publish_key   : 'pub-c-13af87ad-f46a-4974-be1f-96c72515ad27', // Your Pub Key
            subscribe_key : 'sub-c-0c087384-7197-11e9-a1d6-2a8c316da507', // Your Sub Key
            oneway        : true
        });
    
        var ctrl = window.ctrl = CONTROLLER(phone);
        ctrl.ready(function(){
            ctrl.isStreaming(num, function(isOn){
			    console.log("Nama Stream : "+num);
			    if(num){
				    ctrl.joinStream(num);
                }
			    // if (isOn) ctrl.joinStream(num);
			    else alert("User is not streaming!");
		    });
		    addLog("Joining stream  " + num); 
        });
        
	    ctrl.receive(function(session){
	        session.connected(function(session){ 
                video_out.appendChild(session.video); 
                addLog(session.number + " has joined.");
                stream_info.hidden=false;
            });
	        session.ended(function(session) { addLog(session.number + " has left."); });
        });
        
	    ctrl.streamPresence(function(m){
		    here_now.innerHTML=m.occupancy;
		    addLog(m.occupancy + " currently watching.");
	    });
	    return false;
    }

    function getVideo(number){
        return $('*[data-number="'+number+'"]');
    }

    function addLog(log){
	    $('#logs').append("<p>"+log+"</p>");
    }

    function end(){
	    if (!window.phone) return;
	    ctrl.hangup();
        video_out.innerHTML = "none";
        //	phone.pubnub.unsubscribe(); // unsubscribe all?
    }

    function errWrap(fxn, form){
	    try {
		    return fxn(form);
	    } catch(err) {
		    alert("WebRTC is currently only supported by Chrome, Opera, and Firefox");
		    return false;
	    }
    }

</script>