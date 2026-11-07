<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#101a2d">
<title>Live Object Detection</title>
<style>
:root {
  color-scheme: dark;
  --page: #101a2d;
  --panel: #18243a;
  --panel-light: #202e46;
  --line: #33425c;
  --text: #eef4ff;
  --muted: #9eacc2;
  --accent: #70e1c0;
  --accent-dark: #143b3c;
  --danger: #ff8c91;
}
* { box-sizing: border-box; }
body {
  margin: 0;
  min-height: 100vh;
  background: radial-gradient(ellipse at 50% -20%, #263b5a 0, var(--page) 52%);
  color: var(--text);
  font: 16px/1.5 Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}
button, input { font: inherit; }
button {
  min-height: 46px;
  padding: 0 18px;
  border: 1px solid transparent;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: background .18s ease, transform .18s ease, border-color .18s ease;
}
button:hover:not(:disabled) { transform: translateY(-1px); }
button:focus-visible, input:focus-visible { outline: 3px solid #70e1c099; outline-offset: 3px; }
button:disabled { cursor: not-allowed; opacity: .48; }
.primary { color: #10251f; background: var(--accent); }
.primary:hover:not(:disabled) { background: #8af0d2; }
.secondary { color: var(--text); background: var(--panel-light); border-color: var(--line); }
.secondary:hover:not(:disabled) { border-color: #70819b; }
.shell { width: min(1120px, calc(100% - 40px)); margin: 0 auto; padding: 34px 0 52px; }
.topline { display: flex; align-items: center; gap: 10px; color: var(--accent); font-size: .76rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
.topline::before { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); content: ""; }
h1 { margin: 10px 0 6px; font-size: clamp(2rem, 5vw, 3.2rem); letter-spacing: -.045em; line-height: 1.1; }
.intro { max-width: 640px; margin: 0; color: var(--muted); }
.layout { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(270px, .8fr); gap: 18px; margin-top: 28px; align-items: start; }
.card { overflow: hidden; border: 1px solid var(--line); border-radius: 16px; background: #172338eF; box-shadow: 0 18px 55px #050b1580; }
.camera-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 15px 18px; border-bottom: 1px solid var(--line); }
.camera-head strong { font-size: .94rem; }
.status { display: inline-flex; align-items: center; gap: 8px; color: var(--muted); font-size: .82rem; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: #7d8ba1; }
.status[data-state="ready"] { color: var(--accent); }
.status[data-state="ready"] .status-dot { background: var(--accent); box-shadow: 0 0 12px #70e1c088; }
.status[data-state="error"] { color: var(--danger); }
.status[data-state="error"] .status-dot { background: var(--danger); }
.stage {
  position: relative;
  display: grid;
  min-height: 300px;
  aspect-ratio: 4 / 3;
  place-items: center;
  overflow: hidden;
  background: radial-gradient(ellipse, #1a2a41, #0b1220);
}
.stage video, .stage canvas { position: absolute; inset: 0; width: 100%; height: 100%; }
.stage video { object-fit: contain; }
.stage canvas { pointer-events: none; }
.placeholder { z-index: 1; width: min(340px, 85%); padding: 18px; text-align: center; color: var(--muted); }
.placeholder[hidden] { display: none; }
.camera-icon { display: grid; width: 58px; height: 58px; margin: 0 auto 13px; place-items: center; border: 1px solid #40516b; border-radius: 18px; background: #23334d; color: var(--accent); font-size: 1.5rem; }
.placeholder strong { display: block; margin-bottom: 4px; color: var(--text); }
.camera-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; border-top: 1px solid var(--line); }
.camera-foot p { margin: 0; color: var(--muted); font-size: .78rem; }
.controls { padding: 19px; }
.section-title { margin: 0 0 4px; font-size: 1rem; }
.section-subtitle { margin: 0 0 18px; color: var(--muted); font-size: .82rem; }
.button-row { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
.setting { margin-top: 24px; }
.setting-label { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 9px; font-size: .84rem; font-weight: 700; }
.setting-label output { color: var(--accent); }
input[type="range"] { width: 100%; accent-color: var(--accent); cursor: pointer; }
.range-hints { display: flex; justify-content: space-between; margin-top: 2px; color: var(--muted); font-size: .72rem; }
.divider { height: 1px; margin: 22px 0 18px; background: var(--line); }
.detection-summary { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
.detection-summary h2 { margin: 0; font-size: 1rem; }
.count { color: var(--accent); font-size: .82rem; font-weight: 700; }
.detection-list { display: grid; gap: 8px; margin: 13px 0 0; padding: 0; list-style: none; }
.detection-list li { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 11px; border: 1px solid #33425c; border-radius: 9px; background: #202e46; font-size: .83rem; }
.detection-list .label { font-weight: 700; text-transform: capitalize; }
.detection-list .confidence { flex: 0 0 auto; color: var(--accent); font-variant-numeric: tabular-nums; }
.empty-list { margin: 13px 0 0; color: var(--muted); font-size: .82rem; }
.privacy { display: flex; gap: 10px; margin-top: 18px; padding: 12px; border-radius: 10px; background: #101a2d; color: var(--muted); font-size: .75rem; }
.privacy span:first-child { color: var(--accent); }
.notice { min-height: 1.4em; margin: 13px 0 0; color: var(--muted); font-size: .79rem; }
.notice.error { color: var(--danger); }
@media (max-width: 760px) {
  .shell { width: min(620px, calc(100% - 28px)); padding-top: 26px; }
  .layout { grid-template-columns: 1fr; gap: 14px; margin-top: 22px; }
  .stage { min-height: 230px; }
}
@media (max-width: 440px) {
  .camera-head, .camera-foot { align-items: flex-start; }
  .camera-foot { flex-direction: column; }
  .button-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<main class="shell">
  <div class="topline">Vision lab</div>
  <h1>Live object detection</h1>
  <p class="intro">Point your camera at everyday objects and see what the model can recognize in real time.</p>

  <div class="layout">
    <section class="card" aria-label="Camera preview">
      <div class="camera-head">
        <strong>Camera preview</strong>
        <span class="status" id="status" data-state="idle" role="status" aria-live="polite">
          <span class="status-dot" aria-hidden="true"></span><span id="statusText">Camera off</span>
        </span>
      </div>
      <div class="stage" id="stage">
        <video id="video" autoplay muted playsinline aria-label="Live camera video"></video>
        <canvas id="overlay" aria-hidden="true"></canvas>
        <div class="placeholder" id="placeholder">
          <div class="camera-icon" aria-hidden="true">⌁</div>
          <strong>Your camera preview will appear here</strong>
          <span>Start the camera and allow access when your browser asks.</span>
        </div>
      </div>
      <div class="camera-foot">
        <p>Detection runs locally in your browser.</p>
        <p id="modelState">Model not loaded</p>
      </div>
    </section>

    <aside class="card controls" aria-label="Detection controls and results">
      <h2 class="section-title">Controls</h2>
      <p class="section-subtitle">Start your camera to begin identifying objects.</p>
      <div class="button-row">
        <button class="primary" id="startButton" type="button">Start camera</button>
        <button class="secondary" id="stopButton" type="button" disabled>Stop</button>
      </div>
      <div class="setting">
        <label class="setting-label" for="threshold">
          <span>Confidence threshold</span><output id="thresholdValue" for="threshold">50%</output>
        </label>
        <input id="threshold" type="range" min="30" max="90" step="5" value="50">
        <div class="range-hints"><span>Show more</span><span>Higher confidence</span></div>
      </div>
      <p class="notice" id="notice" aria-live="polite"></p>
      <div class="divider"></div>
      <div class="detection-summary">
        <h2>Detected objects</h2>
        <span class="count" id="count">0 objects</span>
      </div>
      <p class="empty-list" id="emptyList">Objects detected by the camera will show up here.</p>
      <ul class="detection-list" id="detectionList" aria-label="Detected objects"></ul>
      <div class="privacy"><span aria-hidden="true">●</span><span>Your video is processed on this device and is not recorded or uploaded.</span></div>
    </aside>
  </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.22.0/dist/tf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd@2.2.3/dist/coco-ssd.min.js"></script>
<script>
const video = document.getElementById("video");
const overlay = document.getElementById("overlay");
const stage = document.getElementById("stage");
const placeholder = document.getElementById("placeholder");
const status = document.getElementById("status");
const statusText = document.getElementById("statusText");
const modelState = document.getElementById("modelState");
const notice = document.getElementById("notice");
const startButton = document.getElementById("startButton");
const stopButton = document.getElementById("stopButton");
const threshold = document.getElementById("threshold");
const thresholdValue = document.getElementById("thresholdValue");
const detectionList = document.getElementById("detectionList");
const emptyList = document.getElementById("emptyList");
const count = document.getElementById("count");
let cameraStream;
let detector;
let running = false;
let starting = false;
let modelPromise;

function setStatus(state, message) {
  status.dataset.state = state;
  statusText.textContent = message;
}

function setNotice(message, isError = false) {
  notice.textContent = message;
  notice.classList.toggle("error", isError);
}

function clearDetections() {
  detectionList.replaceChildren();
  emptyList.hidden = false;
  emptyList.textContent = "Objects detected by the camera will show up here.";
  count.textContent = "0 objects";
  overlay.getContext("2d").clearRect(0, 0, overlay.width, overlay.height);
}

function stopCamera() {
  running = false;
  if (cameraStream) {
    cameraStream.getTracks().forEach(track => track.stop());
    cameraStream = undefined;
  }
  video.pause();
  video.srcObject = null;
  placeholder.hidden = false;
  startButton.disabled = starting;
  stopButton.disabled = true;
  setStatus("idle", "Camera off");
  clearDetections();
}

async function loadDetector() {
  if (detector) return detector;
  if (!window.cocoSsd || !window.tf) {
    throw new Error("The detection model could not load. Check your internet connection and reload the page.");
  }
  if (!modelPromise) {
    modelState.textContent = "Loading model…";
    modelPromise = window.cocoSsd.load();
  }
  try {
    detector = await modelPromise;
    modelState.textContent = "Model ready";
    return detector;
  } catch (error) {
    modelPromise = undefined;
    modelState.textContent = "Model unavailable";
    throw new Error("Could not load the object detection model. Check your internet connection and try again.");
  }
}

function drawPredictions(predictions) {
  const context = overlay.getContext("2d");
  if (overlay.width !== video.videoWidth || overlay.height !== video.videoHeight) {
    overlay.width = video.videoWidth;
    overlay.height = video.videoHeight;
    stage.style.aspectRatio = `${video.videoWidth} / ${video.videoHeight}`;
  }
  context.clearRect(0, 0, overlay.width, overlay.height);
  context.lineWidth = Math.max(2, overlay.width / 320);
  context.font = `600 ${Math.max(14, overlay.width / 42)}px system-ui, sans-serif`;
  const minimumScore = Number(threshold.value) / 100;
  const visible = predictions.filter(item => item.score >= minimumScore);

  visible.forEach(item => {
    const [x, y, width, height] = item.bbox;
    const label = `${item.class} ${Math.round(item.score * 100)}%`;
    const textWidth = context.measureText(label).width;
    const labelHeight = Math.max(23, overlay.width / 32);
    const textY = Math.max(labelHeight, y);
    context.strokeStyle = "#70e1c0";
    context.fillStyle = "#70e1c0";
    context.strokeRect(x, y, width, height);
    context.fillRect(x, textY - labelHeight, textWidth + 14, labelHeight);
    context.fillStyle = "#10251f";
    context.fillText(label, x + 7, textY - labelHeight / 2 + context.measureText("M").width / 2);
  });

  detectionList.replaceChildren();
  emptyList.hidden = visible.length > 0;
  emptyList.textContent = predictions.length ? "No objects meet this confidence threshold." : "No objects detected yet. Try pointing at a well-lit object.";
  count.textContent = `${visible.length} ${visible.length === 1 ? "object" : "objects"}`;
  visible.slice(0, 8).forEach(item => {
    const row = document.createElement("li");
    const label = document.createElement("span");
    const score = document.createElement("span");
    label.className = "label";
    label.textContent = item.class;
    score.className = "confidence";
    score.textContent = `${Math.round(item.score * 100)}%`;
    row.append(label, score);
    detectionList.append(row);
  });
}

async function detectFrame() {
  if (!running || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
    if (running) window.setTimeout(detectFrame, 120);
    return;
  }
  try {
    const predictions = await detector.detect(video, 20, 0.3);
    if (!running) return;
    drawPredictions(predictions);
    window.setTimeout(detectFrame, 120);
  } catch (error) {
    setNotice("Detection stopped because the model encountered an error. Stop the camera and try again.", true);
    stopCamera();
  }
}

async function startCamera() {
  if (starting || running) return;
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    setStatus("error", "Camera unavailable");
    setNotice("This browser does not support camera access. Try a current browser over HTTPS or on localhost.", true);
    return;
  }

  starting = true;
  startButton.disabled = true;
  stopButton.disabled = true;
  setNotice("");
  setStatus("loading", "Starting camera…");
  try {
    cameraStream = await navigator.mediaDevices.getUserMedia({
      audio: false,
      video: { facingMode: "environment" }
    });
    video.srcObject = cameraStream;
    await video.play();
    await loadDetector();
    placeholder.hidden = true;
    running = true;
    setStatus("ready", "Detecting");
    startButton.disabled = true;
    stopButton.disabled = false;
    detectFrame();
  } catch (error) {
    stopCamera();
    setStatus("error", "Could not start");
    if (error.name === "NotAllowedError" || error.name === "PermissionDeniedError") {
      setNotice("Camera access was denied. Allow camera access in your browser settings, then try again.", true);
    } else if (error.name === "NotFoundError" || error.name === "DevicesNotFoundError") {
      setNotice("No camera was found. Connect a camera and try again.", true);
    } else {
      setNotice(error.message || "Unable to start the camera. Check your browser permissions and try again.", true);
    }
  } finally {
    starting = false;
    startButton.disabled = running;
  }
}

startButton.addEventListener("click", startCamera);
stopButton.addEventListener("click", stopCamera);
threshold.addEventListener("input", () => {
  thresholdValue.value = `${threshold.value}%`;
  thresholdValue.textContent = `${threshold.value}%`;
});
window.addEventListener("pagehide", stopCamera);
</script>
</body>
</html>
