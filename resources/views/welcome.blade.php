<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/css/app.css','resources/js/app.js'])
  <title>Feeder Helper</title>
</head>
<body class="min-h-screen bg-gray-50">
  <div class="max-w-3xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow p-6">
      <h1 class="text-2xl font-bold">Feeder Helper</h1>
      <p class="mt-2 text-gray-600">
        Upload XLSX → Validasi → Sync ke Neo Feeder (Web Service)
      </p>

      <div class="mt-6 flex gap-3">
        <a href="/health" class="px-4 py-2 rounded-xl bg-black text-white">Health Check</a>
        <a href="/feeder/ping" class="px-4 py-2 rounded-xl bg-white border">Ping Feeder</a>
      </div>
    </div>
  </div>
</body>
</html>
