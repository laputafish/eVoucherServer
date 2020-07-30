// QRCODE reader Copyright 2011 Lazar Laszlo
// http://www.webqr.com

var gCtx = null;
var gCanvas = null;
var c=0;
var stype=0;
var gUM=false;
var webkit=false;
var moz=false;
var v=null;
var videoDevices = [];
var selectedVideoDeviceId = '';
var workingVideoDeviceId = '';
var switching = false;

const NO_VIDEO_DEVICE = 1;
const BROWSER_NOT_SUPPORTED = 2;

var imghtml='<div id="qrfile"><canvas id="out-canvas" width="320" height="240"></canvas>'+
  '<div id="imghelp">drag and drop a QRCode here'+
  '<br>or select a file'+
  '<input type="file" onchange="handleFiles(this.files)"/>'+
  '</div>'+
  '</div>';

var vidhtml = '<video id="v" autoplay></video>';

function initCanvas(w,h)
{
  // console.log('initCanvas w=' + w)
  // console.log('initCanvas h=' + h)

  gCanvas = document.getElementById("qr-canvas");
  gCanvas.style.width = w + "px";
  gCanvas.style.height = h + "px";
  gCanvas.width = w;
  gCanvas.height = h;

  console.log('gCanvas.style.width = ' + gCanvas.style.width)
  console.log('gCanvas.style.height = ' + gCanvas.style.height)
  console.log('gCanvas.width = ' + gCanvas.width)
  console.log('gCanvas.height = ' + gCanvas.height)

  gCtx = gCanvas.getContext("2d");
  gCtx.clearRect(0, 0, w, h);
}

function captureToCanvas() {
  if (workingVideoDeviceId==='') {
    workingVideoDeviceId = selectedVideoDeviceId
  } else if(workingVideoDeviceId !== selectedVideoDeviceId) {
    workingVideoDeviceId = selectedVideoDeviceId
  } else {
    // for stype==1 only.
    //
    // stype==1: using webcam
    // style==2: using qr-image file
    if (stype != 1)
      return;
    if (gUM) {
      try {
        gCtx.drawImage(v, 0, 0);
        try {
          qrcode.decode();
        }
        catch (e) {
          console.log(e);
          setTimeout(captureToCanvas, 500);
        }
        ;
      }
      catch (e) {
        console.log(e);
        setTimeout(captureToCanvas, 500);
      }
      ;
    }
  }
}

function htmlEntities(str) {
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function read(a)
{
  document.getElementById("result").innerHTML = '- verifying -';
  // console.log('read: a: ', a)
  if (typeof isCorrectRedeemCode === 'function') {
    if (isCorrectRedeemCode(a)) {
      closeCamera()
      // console.log('read => check : valid')
    } else {
      // console.log('read => check : not valid')
    }
  }
}

function isCanvasSupported(){
  var elem = document.createElement('canvas');
  return !!(elem.getContext && elem.getContext('2d'));
}

function success(stream)
{
  // console.log('success: stream: ', stream)
  if (switching) {
    // console.log('success :: is switching')
    // v.pause();
    // v.src = null;
    switching = false
  } else {
    // console.log('success :: not switching')
  }

  v.srcObject = stream;
  v.play();

  gUM = true;
  setTimeout(captureToCanvas, 500);
}

function error(error)
{
  gUM=false;
  return;
}

function startScan()
{
  var errorCode = 0;
  if(isCanvasSupported()) // && window.File && window.FileReader)
  {
    const objOutdiv = document.getElementById('outdiv')
    const w = objOutdiv.clientWidth
    const h = objOutdiv.clientHeight
    objOutdiv.style.width = w + 'px'
    objOutdiv.style.height = h + 'px'

    console.log('outdiv.clientWiddth = ' + w)
    console.log('outdiv.clientHeight = ' + h)
    initCanvas(w, h);
    qrcode.callback = read;
    document.getElementById("mainbody").style.display="inline";
    errorCode = setwebcam();
  }
  else
  {
    errorCode = BROWSER_NOT_SUPPORTED;
    // document.getElementById("mainbody").style.display="inline";
    // document.getElementById("mainbody").innerHTML='<p id="mp1">QR code scanner for HTML5 capable browsers</p><br>'+
    //   '<br><p id="mp2">sorry your browser is not supported</p><br><br>'+
    //   '<p id="mp1">try <a href="http://www.mozilla.com/firefox"><img src="firefox.png"/></a> or <a href="http://chrome.google.com"><img src="chrome_logo.gif"/></a> or <a href="http://www.opera.com"><img src="Opera-logo.png"/></a></p>';
  }
  return errorCode;
}

function switchCam() {
  stype = 0
  switching = true
  // console.log('switchCam: selectedVideoDeviceId = ' + selectedVideoDeviceId)
  if (videoDevices.length > 1) {
    var index = videoDevices.findIndex(item=>item.deviceId==selectedVideoDeviceId)
    // console.log('switchCam: index = ' + index)
    index = (index+1) % videoDevices.length
    selectedVideo = videoDevices[index]
    selectedVideoDeviceId = selectedVideo.deviceId
    const options={'deviceId': {'exact':selectedVideoDeviceId}};
    // const options={'deviceId': {'exact':selectedVideoDeviceId}, 'facingMode':'environment'};

    // console.log('switchCam: after: index = ' + index)
    // console.log('switchCam: after: selectedVideoDeviceId = ' + selectedVideoDeviceId)

    setwebcam2(options);
  }
}

function setwebcam()
{
  var errorCode = 0;
  var options = true;

  console.log('setwebcam()')
  if(navigator.mediaDevices && navigator.mediaDevices.enumerateDevices)
  {
    try {
      videoDevices = [];
      selectedVideoDeviceId = '';
      selectedDevice = null;
      navigator.mediaDevices.enumerateDevices()
        .then(function(devices) {
          console.log('setwebcam:device count = ' + devices.length)
          for (var i = 0; i < devices.length; i++) {
            var device = devices[i]
            console.log('setwebcam: device #' + (i+1) + ': ', device);
            console.log('setwebcam: device #' + (i+1) + ' kind: ' + device.kind);

            if (device.kind === 'videoinput') {
              videoDevices.push(device)
              if(device.label.toLowerCase().search("back") >-1) {
                selectedDevice = device
                // selectedVideoDeviceId = device.deviceId

                // options={'deviceId': {'exact':selectedVideoDeviceId}, 'facingMode':'environment'} ;
                // console.log('setwebcam:found back camera : deviceId: ', device.deviceId);
                // console.log('setwebcam:found back camera : deviceKind: ', device.Kind);
                // console.log('setwebcam:found back camera : deviceLabel: ', device.Label);
              }
            }
          };
          if (selectedDevice) {
            selectedVideoDeviceId = selectedDevice.deviceId;
            options={'deviceId': {'exact':selectedVideoDeviceId}}; // , 'facingMode':'environment'} ;
          }
          console.log('setwebcam: after loop: selectedVideoDeviceId = ' + selectedVideoDeviceId)
          console.log('setwebcam: after loop: videoDevices.length = ' + videoDevices.length)

          if (selectedDevice === null) {
            console.log('setwebcam: selectedVideoDeviceId === ""')
            if (videoDevices.length > 0) {
              var allNoLabel = true;
              for (var j = 0; j < videoDevices.length; j++) {
                if (videoDevices[j].label !== '') {
                  allNoLabel = false;
                  break;
                }
              }

              if (!allNoLabel) {
                selectedDevice = videoDevices[videoDevices.length - 1];
                selectedVideoDeviceId = selectedDevice.deviceId;
                options = {'deviceId': {'exact': selectedVideoDeviceId}}; // , 'facingMode':'environment'};
              }
            }
          }

          console.log('setwebcam: after loop: selectedVideoDeviceId = ' + selectedVideoDeviceId)
          console.log('setwebcam: after loop: videoDevices.length = ' + videoDevices.length)


          if (selectedDevice !== null) {
            console.log('setwebcam :: selectedDevice !== null')
            setwebcam2(options);
          } else {
            console.log('setwebcam :: selectedDevice === null')
            if (typeof showDeviceError === 'function') {
              console.log('setwebcam :: showDeviceErorr is function')
              showDeviceError(true)
            }
            // errorCode = BROWSER_NOT_SUPPORTED;
          }
        });

    }
    catch(e)
    {
      errorCode = BROWSER_NOT_SUPPORTED;
      // console.log('No video device detected!')
      // console.log(e);
    }
  }
  else{
    console.log("Browser: no navigator.mediaDevices.enumerateDevices" );
    errorCode = BROWSER_NOT_SUPPORTED;
    // setwebcam2(options);
  }
  return errorCode;
}

function setwebcam2(options)
{
  if (switching) {
    v.pause()
    v.srcObject.getTracks().forEach(track => track.stop());
    v.srcObject = null;
  }

  document.getElementById("result").innerHTML="- 檢測中 scanning3 -";
  if(stype==1)
  {
    setTimeout(captureToCanvas, 500);
    return;
  }
  var n=navigator;
  document.getElementById("outdiv").innerHTML = vidhtml;
  v = document.getElementById("v");

  if(n.mediaDevices.getUserMedia)
  {
    // console.log('setwebcam2 :: if(n.mediaDevices.getUserMdeia) :: options: ', options)
    n.mediaDevices.getUserMedia({video: options, audio: false}).
    then(function(stream){
      // console.log('setwebcam2 => success')
      success(stream);
    }).catch(function(error){
      // console.log('setwebcam2 :: n.mediaDevices.getUserMedia.then error: ', error)
      alert('error: ', error)
      // error(error)
    });
  }
  else {
    // console.log('setwebcam2 :: !n.mediaDevices.getUserMddia')
    if(n.getUserMedia)
    {
      // console.log('setwebcam2 :: !n.mediaDevices.getUserMedia but n.getUserMedia ok')

      webkit=true;
      n.getUserMedia({video: options, audio: false}, success, error);
    }
    else {
      // console.log('setwebcam2 :: !n.mediaDevices.getUserMedia but n.getUserMedia ok')
      if(n.webkitGetUserMedia)
      {
        webkit=true;
        n.webkitGetUserMedia({video:options, audio: false}, success, error);
      }
    }
  }

  // document.getElementById("qrimg").style.opacity=0.2;
  // document.getElementById("webcamimg").style.opacity=1.0;

  stype=1;
  setTimeout(captureToCanvas, 500);
}
