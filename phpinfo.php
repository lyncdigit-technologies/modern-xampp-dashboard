<?php
ob_start();
phpinfo();
$phpinfo = ob_get_clean();

// Clean up and Bootstrapify
$phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
$phpinfo = preg_replace('/<table(.*?)>/', '<table class="table table-bordered table-striped table-hover w-100" $1>', $phpinfo);
$phpinfo = preg_replace('/<h2>(.*?)<\/h2>/', '<h3 class="mt-4">$1</h3>', $phpinfo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PHP Info - Lyncdigit</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 50px 0; }
    .logo { height: 50px; }
    h1 { font-size: 2rem; margin-bottom: 1rem; }
    table { font-size: 0.875rem; }
    th { background: #f8f9fa; }
    #searchBox { margin: 20px 0; }
    @media print { #controls { display: none; } }
  </style>
</head>
<body>
  <div class="container-fluid px-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4" id="controls">
      <div class="d-flex align-items-center">
        <img src="https://lyncdigit.com/assets/images/logo.png" class="logo me-3" alt="Lyncdigit Logo">
        <h1 class="mb-0">🚀 PHP Info</h1>
      </div>
      <div class="d-flex gap-2">
        <button onclick="printPage()" class="btn btn-outline-secondary">🖨️ Export PDF</button>
        <button onclick="downloadHTML()" class="btn btn-outline-primary">💾 Export HTML</button>
      </div>
    </div>

    <!-- Search -->
    <input type="text" id="searchBox" class="form-control" placeholder="🔍 Search PHP Info...">

    <!-- PHP Info -->
    <div id="phpinfo-content">
      <?= $phpinfo ?>
    </div>

    <!-- Outro Section -->
    <div class="mt-5">
      <h3 class="mt-4">📘 About This Dashboard</h3>
      <table class="table table-bordered table-striped">
        <tbody>
          <tr>
            <th>Tool Name</th>
            <td>Localhost PHP Info Panel</td>
          </tr>
          <tr>
            <th>Stack</th>
            <td>PHP, Bootstrap 5, JavaScript</td>
          </tr>
          <tr>
            <th>Features</th>
            <td>Bootstrap-styled PHP info, search/filter, HTML/PDF export</td>
          </tr>
          <tr>
            <th>Status</th>
            <td>✅ Functional | 🧪 Extendable</td>
          </tr>
        </tbody>
      </table>

      <div class="p-4 bg-light rounded">
        <h4 class="mb-2">About <span class="text-primary">Lyncdigit</span></h4>
        <p class="mb-0">
          <strong>Lyncdigit Technologies</strong> is a tech-driven company focused on delivering powerful, user-friendly solutions to streamline development workflows. From full-stack development and infrastructure automation to custom CRMs and internal dashboards, our mission is to make technology seamless and productive.
        </p>
        <p class="mt-2">
          🚀 Visit us at <a href="https://lyncdigit.com" target="_blank">lyncdigit.com</a>
        </p>
      </div>
    </div>
  </div>

  <script>
    const searchInput = document.getElementById('searchBox');
    const content = document.getElementById('phpinfo-content');

    searchInput.addEventListener('input', () => {
      const term = searchInput.value.toLowerCase();
      const rows = content.querySelectorAll('tr');

      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
      });

      const headers = content.querySelectorAll('h3');
      headers.forEach(header => {
        const table = header.nextElementSibling;
        if (table && table.tagName === 'TABLE') {
          const visibleRows = table.querySelectorAll('tr:not([style*="display: none"])').length;
          header.style.display = visibleRows ? '' : 'none';
        }
      });
    });

    function printPage() {
      window.print();
    }

    function downloadHTML() {
      const blob = new Blob([`<!DOCTYPE html><html><head><meta charset="UTF-8"><title>PHP Info</title></head><body>${content.innerHTML}</body></html>`], { type: 'text/html' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'phpinfo.html';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    }
  </script>
</body>
</html>
