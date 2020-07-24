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

var imghtml='<div id="qrfile"><canvas id="out-canvas" width="320" height="240"></canvas>'+
  '<div id="imghelp">drag and drop a QRCode here'+
  '<br>or select a file'+
  '<input type="file" onchange="handleFiles(this.files)"/>'+
  '</div>'+
  '</div>';

var vidhtml = '<video id="v" autoplay></video>';

function initCanvas(w,h)
{
  console.log('initCanvas w=' + w)
  console.log('initCanvas h=' + h)

  gCanvas = document.getElementById("qr-canvas");
  gCanvas.style.width = w + "px";
  gCanvas.style.height = h + "px";
  gCanvas.width = w;
  gCanvas.height = h;
  gCtx = gCanvas.getContext("2d");
  gCtx.clearRect(0, 0, w, h);
}

function captureToCanvas() {
  // for stype==1 only.
  //
  // stype==1: using webcam
  // style==2: using qr-image file
  if(stype!=1)
    return;
  if(gUM)
  {
    try{
      gCtx.drawImage(v,0,0);
      try{
        qrcode.decode();
      }
      catch(e){
        console.log(e);
        setTimeout(captureToCanvas, 500);
      };
    }
    catch(e){
      console.log(e);
      setTimeout(captureToCanvas, 500);
    };
  }
}

function htmlEntities(str) {
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function read(a)
{
  document.getElementById("result").innerHTML = '- verifying -';
  console.log('read: a: ', a)
  if (typeof isValid === 'function') {
    if (isValid(a)) {
      closeCamera()
      console.log('read => check : valid')
    } else {
      console.log('read => check : not valid')
    }
  }
}

function isCanvasSupported(){
  var elem = document.createElement('canvas');
  return !!(elem.getContext && elem.getContext('2d'));
}

function success(stream)
{
  v.srcObject = stream;
  v.play();

  gUM=true;
  setTimeout(captureToCanvas, 500);
}

function error(error)
{
  gUM=false;
  return;
}

function startScan()
{
  load();
}

function load()
{
  if(isCanvasSupported()) // && window.File && window.FileReader)
  {
    const w = document.getElementById('outdiv').clientWidth
    const h = document.getElementById('outdiv').clientHeight
    initCanvas(w, h);
    qrcode.callback = read;
    document.getElementById("mainbody").style.display="inline";
    setwebcam();
  }
  else
  {
    document.getElementById("mainbody").style.display="inline";
    document.getElementById("mainbody").innerHTML='<p id="mp1">QR code scanner for HTML5 capable browsers</p><br>'+
      '<br><p id="mp2">sorry your browser is not supported</p><br><br>'+
      '<p id="mp1">try <a href="http://www.mozilla.com/firefox"><img src="firefox.png"/></a> or <a href="http://chrome.google.com"><img src="chrome_logo.gif"/></a> or <a href="http://www.opera.com"><img src="Opera-logo.png"/></a></p>';
  }
}

function setwebcam()
{
  var options = true;

  if(navigator.mediaDevices && navigator.mediaDevices.enumerateDevices)
  {
    try{
      navigator.mediaDevices.enumerateDevices()
        .then(function(devices) {
          console.log('setwebcam: device count = ' + devices.length)
          devices.forEach(function(device) {
            if (device.kind === 'videoinput') {
              if(device.label.toLowerCase().search("back") >-1) {
                options={'deviceId': {'exact':device.deviceId}, 'facingMode':'environment'} ;
                console.log('setwebcam: found back camera : deviceId: ', device.deviceId);
                console.log('setwebcam: found back camera : deviceKind: ', device.Kind);
                console.log('setwebcam: found back camera : deviceLabel: ', device.Label);
              }
            }
            document.getElementById('deviceId').innerText = device.deviceId;
            document.getElementById('deviceKind').innerText = device.kind;
            document.getElementById('deviceLabel').innerText = device.kind;
          });
          setwebcam2(options);
        });
    }
    catch(e)
    {
      console.log(e);
    }
  }
  else{
    console.log("no navigator.mediaDevices.enumerateDevices" );
    setwebcam2(options);
  }

}

function setwebcam2(options)
{
  console.log('setwebcam2: options: ', options);
  document.getElementById("result").innerHTML="- 檢測中 scanning -";
  if(stype==1)
  {
    setTimeout(captureToCanvas, 500);
    return;
  }
  var n=navigator;

  // output pane
  var outdiv = document.getElementById("outdiv")
  outdiv.innerHTML = vidhtml;
  var w = outdiv.clientWidth;
  var h = outdiv.clientHeight;

  // Video html region
  v=document.getElementById("v");
  v.style.width = w + 'px';
  v.style.height = h + 'px';

  if(n.mediaDevices && n.mediaDevices.getUserMedia)
  {
    n.mediaDevices.getUserMedia({video: options, audio: false}).
    then(function(stream){
      success(stream);
    }).catch(function(error){
      alert('1error: ', error)
      return
      // error(error)
    });
  }
  else
  if(n.getUserMedia)
  {
    webkit=true;
    n.getUserMedia({video: options, audio: false}, success, error);
  }
  else
  if(n.webkitGetUserMedia)
  {
    webkit=true;
    n.webkitGetUserMedia({video:options, audio: false}, success, error);
  }

  // document.getElementById("qrimg").style.opacity=0.2;
  // document.getElementById("webcamimg").style.opacity=1.0;

  stype=1;
  setTimeout(captureToCanvas, 500);
}
