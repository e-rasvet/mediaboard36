<?php

require_once("../../config.php");
require_once("lib.php");

$id             = optional_param('id', NULL, PARAM_CLEAN); 
$slides         = optional_param('slides', NULL, PARAM_CLEAN); 
$userid         = optional_param('userid', NULL, PARAM_CLEAN);
$municalidjpg   = optional_param('unicalidjpg', NULL, PARAM_CLEAN);
$municalidmp3   = optional_param('unicalidmp3', NULL, PARAM_CLEAN);
$cid            = optional_param('cid', NULL, PARAM_CLEAN);

if ($id) {
    if (! $cm = get_coursemodule_from_id('mediaboard', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        error('Course is misconfigured');
    }

    if (! $mediaboard = $DB->get_record('mediaboard', array('id' => $cm->instance))) {
        error('Course module is incorrect');
    }
} else {
    error('You must specify a course_module ID or an instance ID');
}

$siteid = explode ("/", $CFG->wwwroot);
$siteid = str_replace (".", "_", $siteid[2]);

$mediadata = "";

$updir    = $CFG->dataroot."/presentation/{$cid}/temp";

if (mediaboard_get_browser() == "mobileios" || mediaboard_get_browser() == 'android') {
  for ($i=1; $i <= $slides; $i++) {
      $imagename   = 'img'.$i;
      $unicalidjpg = $municalidjpg.($i-1);
      $unicalidmp3 = $municalidmp3.($i-1);
      
      $filename = str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", time()).'_'.$i;
      
      $o  = "";
      
      $o .= html_writer::tag("div", html_writer::tag("h3", "Slide #".$i));
      
      if ($mediaboard->presetimages == 0) {
        $o .= html_writer::start_tag("div", array("style"=>"float:left;width:250px;margin-left:10px"));
        $o .= "Select image for slide <br />".html_writer::empty_tag("input", array("type" => "file", "name" => "i_image[{$unicalidjpg}]", "accept"=>"image/*", "capture"=>"camera"));
        $o .= html_writer::end_tag("div");
      }

      $o .= html_writer::start_tag("div", array("style"=>"float:left;width:250px;margin-left:10px"));
      //$o .= "Record caption for slide<br />".html_writer::empty_tag("input", array("type" => "file", "name" => "i_audio[{$unicalidmp3}]", "accept"=>"audio/*", "capture"=>"microphone"));
      $o .= "Record caption for slide<br />".html_writer::empty_tag("input", array("type" => "file", "name" => "i_audio[{$unicalidmp3}]", "accept"=>"video/*"));
      $o .= html_writer::end_tag("div");
      
      
      $o .= html_writer::start_tag("div");
      
      $o .= html_writer::start_tag("div", array("style"=>"float:left"));
      
      $o .= html_writer::tag("div", "Use voice record ".html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'usevoice['.$unicalidmp3.']', 'value'=> 1, 'checked'=>'checked')));
      
      if ($mediaboard->presetimages == 0)
        $o .= html_writer::tag("div", "", array('id'=>'slide_preview_'.$i));
      else
        $o .= html_writer::tag("div", html_writer::empty_tag('img', array('src'=>new moodle_url('/mod/mediaboard/showslidepreview.php', array("id" => $mediaboard->{$imagename})), "style"=>"width:150px")), array('id'=>'slide_preview_'.$i));
      
      $o .= html_writer::end_tag("div");
      
      $o .= html_writer::end_tag("div");
      
      $o .= html_writer::tag("div", "", array('style'=>'clear:both'));
      
      $o .= html_writer::empty_tag("hr");
      $o .= html_writer::empty_tag("br");
        
      echo $o;
  }
} else {

  echo html_writer::script('
  function callbackjs(e){
    /*
    * Speech to text box stop
    */
    obj = JSON.parse(e.data);
    var oldid = $("input[name=\'unicalsmp3["+(obj.i - 1)+"]\']").val();
    
    $("input[name=\'usevoice["+oldid+"]\']").attr("name", "usevoice["+obj.id+"]");
    
    $("input[name=\'unicalsmp3["+(obj.i - 1)+"]\']").val(obj.id);
  }
  ');

  for ($i=1; $i <= $slides; $i++) {
      $imagename   = 'img'.$i;
      $unicalidjpg = $municalidjpg.($i-1);
      $unicalidmp3 = $municalidmp3.($i-1);
      
      $filename = str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", time()).'_'.$i;
      
      $o  = "";
      
      $o .= html_writer::tag("div", html_writer::tag("h3", "Slide #".$i));
      
      if ($mediaboard->presetimages == 0)
        $o .= html_writer::tag("div", html_writer::link("#", 'Select image (*.jpg) for slide.', array('id'=>'attachment_upload_'.$i, 'class'=>'showarrow')));
      
      
      $o .= html_writer::start_tag("div");
      
      $o .= html_writer::tag("div", "Record caption for slide", array("style"=>"float:left"));
      
      
      $o .= html_writer::start_tag("div", array("style"=>"float:left;width:400px;margin-left:10px"));
      //$o .= html_writer::script('var fn = function() {var att = { data:"'.(new moodle_url("/mod/mediaboard/js/recorder.swf")).'", width:"350", height:"200"};var par = { flashvars:"rate=44&gain=50&prefdevice=&loopback=no&echosupression=yes&silencelevel=0&updatecontrol=poodll_recorded_file&callbackjs=poodllcallback&posturl='.(new moodle_url("/mod/mediaboard/uploadmp3.php")).'&p1='.$id.'&p2='.$USER->id.'&p3='.$unicalidmp3.'&p4='.$filename.'&autosubmit=true&debug=false&lzproxied=false" };var id = "mp3_flash_recorder_'.$i.'";var myObject = swfobject.createSWF(att, par, id);};swfobject.addDomLoadEvent(fn);function poodllcallback(args){console.log(args);}');
      
      //261456033

      /*
       * Old way
       */
      //$o .= html_writer::script('var flashvars={};flashvars.gain=35;flashvars.rate=44;flashvars.call="callbackjs";flashvars.name = "'.$filename.'";flashvars.p = "'.urlencode(json_encode(array("id"=>$id, "userid"=>$USER->id, "i"=>$i))).'";flashvars.url = "'.urlencode(new moodle_url("/mod/mediaboard/uploadmp3.php")).'";swfobject.embedSWF("'.(new moodle_url("/mod/mediaboard/js/recorder.swf")).'", "mp3_flash_recorder_'.$i.'", "220", "200", "9.0.0", "expressInstall.swf", flashvars);');
      $o .= '<object type="application/x-shockwave-flash" data="'.(new moodle_url("/mod/mediaboard/js/recorder.swf")).'" id="mp3_flash_recorder_'.$i.'" width="220" height="200">
    <param name=\'movie\' value="'.(new moodle_url("/mod/mediaboard/js/recorder.swf")).'"/>
    <param name=\'bgcolor\' value="#999999"/>
    <param name=\'FlashVars\' value="gain=35&rate=44&call=callbackjs&name='.$filename.'&p='.urlencode(json_encode(array("id"=>$id, "userid"=>$USER->id, "i"=>$i))).'&url='.urlencode(new moodle_url("/mod/mediaboard/uploadmp3.php")).'" />
    <param name=\'allowscriptaccess\' value="sameDomain"/>
</object>';



/*
      $additionalCodeSpeechToTextBox = '<textarea id="speechtext" style="width: 650px;height: 40px;margin: 0 0 0 8px;" readonly></textarea>';

      $o .= '

  <div style="font-size: 21px;line-height: 40px;color: #333;">Record</div>

  <img src="img/spiffygif_30x30.gif" style="display:none;" id="html5-mp3-loader"/>
  <button onclick="startRecording(this);" id="btn_rec" disabled>record</button>
  <button onclick="stopRecording(this);" id="btn_stop" disabled>stop</button>

  <div style="margin: 20px 0;">'.$additionalCodeSpeechToTextBox.'</div>

  <div style="font-size: 21px;line-height: 40px;color: #333;">Recordings</div>
  <ul id="recordingslist" style="list-style-type: none;"></ul>

  <div style="font-size: 21px;line-height: 40px;color: #333;display:none;">Log</div>
  <pre id="log" style="display:none"></pre>

  <script>

  $(".selectaudiomodel").click(function(){
    $("#audioshadowmp3").attr("src", $(this).parent().find("audio").attr("src"));
    __log($(this).parent().find("audio").attr("src"));
  });

  function __log(e, data) {
    log.innerHTML += "\n" + e + " " + (data || \'\');
  }

  var audio_context;
  var recorder;

  function startUserMedia(stream) {
    var input = audio_context.createMediaStreamSource(stream);
    __log(\'Media stream created.\' );
    __log("input sample rate " +input.context.sampleRate);

    //input.connect(audio_context.destination);
    //__log(\'Input connected to audio context destination.\');

    recorder = new Recorder(input, {
                  numChannels: 1,
                  sampleRate: 48000,
                });
    __log(\'Recorder initialised.\');
  }

  function startRecording(button) {
    recorder.startRecording();
    button.disabled = true;
    button.nextElementSibling.disabled = false;
    __log(\'Recording...\');
  }

  function stopRecording(button) {
    recorder.finishRecording();
    button.disabled = true;
    button.previousElementSibling.disabled = false;
    __log(\'Stopped recording.\');
  }

  window.onload = function init() {
    // navigator.getUserMedia shim
    navigator.getUserMedia =
      navigator.getUserMedia ||
      navigator.webkitGetUserMedia ||
      navigator.mozGetUserMedia ||
      navigator.msGetUserMedia;
    
    // URL shim
    window.URL = window.URL || window.webkitURL;
    
    // audio context + .createScriptProcessor shim
    var audioContext = new AudioContext;
    if (audioContext.createScriptProcessor == null)
      audioContext.createScriptProcessor = audioContext.createJavaScriptNode;
    
    var testTone = (function() {
      var osc = audioContext.createOscillator(),
          lfo = audioContext.createOscillator(),
          ampMod = audioContext.createGain(),
          output = audioContext.createGain();
      lfo.type = \'square\';
      lfo.frequency.value = 2;
      osc.connect(ampMod);
      lfo.connect(ampMod.gain);
      output.gain.value = 0.5;
      ampMod.connect(output);
      osc.start();
      lfo.start();
      return output;
    })();
    
    

    
    var testToneLevel = audioContext.createGain(),
        microphone = undefined,     // obtained by user click
        microphoneLevel = audioContext.createGain(),
        mixer = audioContext.createGain();
    
    testTone.connect(testToneLevel);
    testToneLevel.gain.value = 0;
    //testToneLevel.connect(mixer);
    microphoneLevel.gain.value = 0.5;
    microphoneLevel.connect(mixer);
    //mixer.connect(audioContext.destination);

      if (microphone == null)
        navigator.getUserMedia({ audio: true },
          function(stream) {
            microphone = audioContext.createMediaStreamSource(stream);
            microphone.connect(microphoneLevel);
          },
          function(error) {
          console.log("Could not get audio input.");
            audioRecorder.onError(audioRecorder, "Could not get audio input.");
          });
    
    
        recorder = new WebAudioRecorder(mixer, {
          workerDir: "js/"
        });
        
        recorder.setEncoding("mp3");
        
          recorder.setOptions({
        timeLimit: 300,
        mp3: { bitRate: 64 }
      });
    
    recorder.onComplete = function(recorder, blob) {
      window.LatestBlob = blob;
      
      var time = new Date(),
      url = URL.createObjectURL(blob),
      html = "<p recording=\'" + url + "\'>" +
             "<audio controls src=\'" + url + "\'></audio> " +
             "</p>";
      
      $("#recordingslist").html(html);
                    
      //saveRecording(blob, recorder.encoding);
      uploadAudio(blob);
      

    };
    
  };
  
  	
	function uploadAudio(mp3Data){
		var reader = new FileReader();
		reader.onload = function(event){
			var fd = new FormData();
			var mp3Name = encodeURIComponent(\'audio_recording_\' + new Date().getTime() + \'.mp3\');
			console.log("mp3name = " + mp3Name);
			fd.append(\'name\', mp3Name);
			fd.append(\'p\', $(\'#audioshadowmp3\').attr("data-url"));
			fd.append(\'audio\', event.target.result);
			$.ajax({
				type: \'POST\',
				url: \'uploadmp3.php\',
				data: fd,
				processData: false,
				contentType: false
			}).done(function(data) {
				//console.log(data);
				obj = JSON.parse(data);
				$("#id_submitfile").val(obj.id);
				
				log.innerHTML += "\n" + data;
			});
		};      
		reader.readAsDataURL(mp3Data);
	}

  function jInit(){
      audio = $("#audioshadowmp3");
      addEventHandlers();
  }

  function addEventHandlers(){
      $("#btn_rec").click(startAudio);
      $("#btn_stop").click(stopAudio);
  }

  function loadAudio(){
      audio.bind("load",function(){
        __log(\'MP3 Audio Loaded succesfully\');
        $(\'#btn_rec\').removeAttr( "disabled" );
      });
      audio.trigger(\'load\');
      //startAudio()
  }

  function startAudio(){
      __log(\'MP3 Audio Play\');
      audio.trigger(\'play\');
  }

  function pauseAudio(){
      __log(\'MP3 Audio Pause\');
      audio.trigger(\'pause\');
  }

  function stopAudio(){
      pauseAudio();
      audio.prop("currentTime",0);
  }

  function forwardAudio(){
      pauseAudio();
      audio.prop("currentTime",audio.prop("currentTime")+5);
      startAudio();
  }

  function backAudio(){
      pauseAudio();
      audio.prop("currentTime",audio.prop("currentTime")-5);
      startAudio();
  }

  function volumeUp(){
      var volume = audio.prop("volume")+0.2;
      if(volume >1){
        volume = 1;
      }
      audio.prop("volume",volume);
  }

  function volumeDown(){
      var volume = audio.prop("volume")-0.2;
      if(volume <0){
        volume = 0;
      }
      audio.prop("volume",volume);
  }

  function toggleMuteAudio(){
      audio.prop("muted",!audio.prop("muted"));
  }

  $( document ).ready(function() {
     jInit();
     loadAudio();

     //$("#id_Recording").find(".fitemtitle").append(\'<img src="img/spiffygif_30x30.gif" style="display:none;" id="html5-mp3-loader"/>\');
  });
</script>';

      $o .= ' <audio src="" id="audioshadowmp3" autobuffer="autobuffer" data-url="' . urlencode(json_encode(array("id" => $id, "userid" => $USER->id))) . '"></audio>
                  ';
*/

      //$o .= '<div id="mp3_flash_recorder"></div><div id="mp3_flash_records" style="margin:20px 0;"></div>';

      $o .= html_writer::tag("div", "", array("id"=>"mp3_flash_recorder_".$i, "style"=>"float:left"));
      $o .= html_writer::end_tag("div");
      
      
      $o .= html_writer::start_tag("div", array("style"=>"float:left"));
      
      $o .= html_writer::tag("div", "Use voice record ".html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'usevoice['.$unicalidmp3.']', 'value'=> 1, 'checked'=>'checked')));
      
      if ($mediaboard->presetimages == 0)
        $o .= html_writer::tag("div", "", array('id'=>'slide_preview_'.$i));
      else
        $o .= html_writer::tag("div", html_writer::empty_tag('img', array('src'=>new moodle_url('/mod/mediaboard/showslidepreview.php', array("id" => $mediaboard->{$imagename})), "style"=>"width:150px")), array('id'=>'slide_preview_'.$i));
      
      $o .= html_writer::end_tag("div");
      
      $o .= html_writer::end_tag("div");
      
      $o .= html_writer::tag("div", "", array('style'=>'clear:both'));
      
      $o .= html_writer::empty_tag("hr");
      $o .= html_writer::empty_tag("br");
        
      echo $o;
  }
}

echo "<center>".$mediadata."<input type=\"hidden\" value=\"".$slides."\" name=\"slides\" /></center>";

