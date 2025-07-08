<?php
$project = $_GET['project'] ?? '';
$basePath = realpath("C:/xampp/htdocs/$project");
if (!$project || !is_dir($basePath)) die("Invalid project.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Project: <?= htmlspecialchars($project) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    #fileTree { height: 500px; overflow-y: auto; border: 1px solid #ccc; }
    #editor { height: 500px; border: 1px solid #ccc; }
    .tab { padding: 4px 8px; cursor: pointer; background: #eee; border: 1px solid #ccc; margin-right: 4px; display: inline-block; }
    .tab.active { background: #ddd; font-weight: bold; }
  </style>
</head>
<body>
<div class="container-fluid py-3">
  <h3 class="mb-3">📝 Editing: <?= htmlspecialchars($project) ?></h3>
  <div class="row">
    <div class="col-md-3">
      <h5>📁 Files</h5>
      <div class="mb-2">
        <button class="btn btn-sm btn-outline-primary" onclick="newFile()">➕ File</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="newFolder()">📁 Folder</button>
      </div>
      <ul id="fileTree" class="list-group"></ul>
    </div>
    <div class="col-md-9">
      <div id="tabs" class="mb-2"></div>
      <div id="editor">// Open a file to start editing</div>
      <div class="mt-2">
        <button class="btn btn-success" onclick="saveFile()">💾 Save</button>
        <button class="btn btn-danger" onclick="deleteFile()">🗑️ Delete</button>
        <button class="btn btn-warning" onclick="renameFile()">✏️ Rename</button>
      </div>
    </div>
  </div>
</div>

<!-- Monaco -->
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js"></script>
<script>
let editor, currentFile = '', tabs = {}, project = "<?= $project ?>";

require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' }});
require(['vs/editor/editor.main'], function () {
  editor = monaco.editor.create(document.getElementById('editor'), {
    value: '',
    language: 'php',
    theme: 'vs-dark'
  });
  loadTree();
});

function loadTree() {
  fetch(`file_api.php?action=list&project=${project}`)
    .then(res => res.json())
    .then(files => {
      const ul = document.getElementById('fileTree');
      ul.innerHTML = '';
      files.forEach(file => {
        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-action';
        li.textContent = file;
        li.onclick = () => openTab(file);
        ul.appendChild(li);
      });
    });
}

function openTab(filename) {
  if (tabs[filename]) {
    setActiveTab(filename);
    return;
  }
  tabs[filename] = { content: '' };
  const tab = document.createElement('div');
  tab.className = 'tab';
  tab.innerText = filename.split('/').pop();
  tab.onclick = () => setActiveTab(filename);
  tab.dataset.file = filename;
  document.getElementById('tabs').appendChild(tab);
  loadFile(filename);
  setActiveTab(filename);
}

function setActiveTab(filename) {
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  const tab = [...document.querySelectorAll('.tab')].find(t => t.dataset.file === filename);
  if (tab) tab.classList.add('active');
  currentFile = filename;
  loadFile(filename);
}

function loadFile(filename) {
  fetch(`file_api.php?action=read&project=${project}&file=${encodeURIComponent(filename)}`)
    .then(res => res.text())
    .then(code => {
      editor.setValue(code);
    });
}

function saveFile() {
  if (!currentFile) return alert("No file open");
  fetch('file_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'save',
      project,
      file: currentFile,
      content: editor.getValue()
    })
  }).then(res => res.text()).then(alert);
}

function deleteFile() {
  if (!currentFile) return;
  if (!confirm(`Delete ${currentFile}?`)) return;
  fetch(`file_api.php?action=delete&project=${project}&file=${encodeURIComponent(currentFile)}`)
    .then(res => res.text())
    .then(msg => {
      alert(msg);
      delete tabs[currentFile];
      currentFile = '';
      document.getElementById('editor').innerText = '';
      loadTree();
      document.getElementById('tabs').innerHTML = '';
    });
}

function renameFile() {
  if (!currentFile) return;
  const newName = prompt("New file name (relative):", currentFile);
  if (!newName || newName === currentFile) return;
  fetch('file_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'rename',
      project,
      old: currentFile,
      new: newName
    })
  }).then(res => res.text()).then(msg => {
    alert(msg);
    loadTree();
    document.getElementById('tabs').innerHTML = '';
    currentFile = '';
    tabs = {};
  });
}

function newFile() {
  const name = prompt("Enter new file path:");
  if (!name) return;
  fetch('file_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'create_file',
      project,
      file: name
    })
  }).then(res => res.text()).then(msg => {
    alert(msg);
    loadTree();
  });
}

function newFolder() {
  const name = prompt("Enter new folder path:");
  if (!name) return;
  fetch('file_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'create_folder',
      project,
      folder: name
    })
  }).then(res => res.text()).then(msg => {
    alert(msg);
    loadTree();
  });
}
</script>
</body>
</html>
