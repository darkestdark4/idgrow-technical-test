# IDGrow Technical Test

Pengerjaan API untuk kebutuhan technical test dari IDGrow.

Link Postman : https://documenter.getpostman.com/view/4054916/2sB34iieH7

## Spesifikasi & Versi

- PHP : 8.3
- Laravel : 12
- MySQL: 8.0
- Nginx

## Cara Install

- Download repository terlebih dahulu
- Setelah download, maka masuk ke dalam direktori repositori terkait, kemudian jalankan perintah ```docker-compose build``` untuk membangun image sesuai dengan isian yang ada di Dockerfile
- Lalu, jika sudah build, maka langkah selanjutnya jalankan perintah ```docker-compose up -d``` untuk menjalankan service background
- Jika sudah, maka salin file .env.example, lalu isi dengan ketentuan dibawah ini
  ```
  DB_CONNECTION=mysql
  DB_HOST=db
  DB_PORT=3306
  DB_DATABASE=idgrow_test
  DB_USERNAME=idgrow
  DB_PASSWORD=idgrow_user
  ```
- Lalu install dependensi laravel dengan perintah ```docker-compose exec app composer install```
- Kemudian generate application key ```docker-compose exec app php artisan key:generate```
- Lalu jalankan proses migrasi DB ```docker-compose exec app php artisan migrate```
- Website dapat diakses melalui url http://localhost:8080
