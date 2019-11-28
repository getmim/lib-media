# lib-media

Adalah module yang bertugas menangani service file upload.

## Instalasi

Jalankan perintah di bawah di folder aplikasi:

```
mim app install lib-media
```

## Konfigurasi

Tambahkan konfigurasi seperti di bawah pada aplikasi/module untuk menentukan
handler yang akan digunakan untuk menangani file. Masing-masing handler akan
dipanggil sampai menemukan handler yang tidak mengembalikan nilai null.

```php
return [
    'libMedia' => [
        'handlers' => [
            'name' => 'Class'
        ]
    ]
];
```

## Custom Handler

Untuk membuatkan file handler, pastikan class tersebut mengimplementasikan
interface `LibMedia\Iface\Handler`. Dan tambahkan method seperti di bawah:

### get(object $file): ?object

Fungsi yang akan dipanggil untuk menggenerasi file compresi, dan resizes. Method
ini akn di panggil dengan parameter seperti di bawah:

```php
$params = (object)[
    'file' => 'aa/bb/cc/dd/filename.jpg',
    'size' => [
        'width' => 100,
        'height' => 150
    ]
];
```

Fungsi tersebut diharapkan mengembalikan data seperti berikut:

```php
return (object)[
    'none' => 'http://target.aa/bb/dd/filename.jpg',
    'webp' => 'http://target.aa/bb/dd/filename.jpg.webp',
    'size' => [
        'width' => 100,
        'height' => 150
    ]
];
```

Ketika file diminta, system mengharapkan handler membuatkan file kompresi brotli,
dan gzip untuk digunakan front-end.

## Formatter

Jika module `lib-formatter` terpasang, maka module ini menambah 2 tipe format sebagai berikut:

### media

Mengubah nilai suatu properti menjadi object media untuk mempermudah mendapatkan nilai-nilai
media:

```php
'field' => [
    'type' => 'media'
]
```

Objek yang dihasilkan bisa digunakan untuk mendapatkan suatu ukuran gambar atau kompresi webp dengan
perintah seperti di bawah:

```php
$field->_100x50;
```

Aksi diatas akan mengembalikan url file gambar untuk ukuran gambar lebar 100 dan tinggi 50. Untuk
mendapatkan file webp, gunakan perintah seperti di bawah:

```php
$field->webp;
$field->_100x50->webp;
```

### media-list

Mengubah nilai menjadi array media. Format tipe ini mengharapkan nilai suatu properti adalah string
dengan suatu separator.

```php
'field' => [
    'type' => 'media-list',
    'separator' => ',' // PHP_EOL, |
]
```