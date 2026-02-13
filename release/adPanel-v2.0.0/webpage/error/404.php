<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - CV. Magz Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Open Sans', sans-serif;
        }
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            max-width: 500px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #495057;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-card card">
                <div class="card-body p-5">
                    <div class="error-code">404</div>
                    <h1 class="error-title">Halaman Tidak Ditemukan</h1>
                    <p class="error-message">
                        Maaf, halaman yang Anda cari tidak dapat ditemukan.<br>
                        Mungkin halaman telah dipindahkan atau dihapus.
                    </p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/" class="btn btn-primary btn-lg me-md-2">Kembali ke Beranda</a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>