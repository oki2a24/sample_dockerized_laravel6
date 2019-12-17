# sample_dockerized_laravel6
Laravel6 を Docker 化するサンプルです。

## Docker 化の実行
```bash
# ビルド
docker build -t sample_dockerized_laravel6 .

# 動作確認
# コンテナ内の SQLite を使用する場合
docker container run -d --rm -p 80:80 --name sample_dockerized_laravel6 sample_dockerized_laravel6:latest

# データベースの指定例
docker container run -d --rm -p 80:80 --name sample_dockerized_laravel6 --env DATABASE_URL=mysql://root:password@mysqlhost/forge?charset=UTF-8 sample_dockerized_laravel6:latest

# コンテナ内に入る
docker container exec -it sample_dockerized_laravel6 bash

# 停止
docker container stop sample_dockerized_larave6
```

## Docker Compose での開発
``` bash
# ビルド
docker-compose -f docker-compose.develop.yml build

# 動作確認
docker-compose -f docker-compose.develop.yml up -d

# コンテナ内に入る
docker-compose exec app bash

# セットアップ (コンテナ内で実行)
composer install
php artisan key:generate
touch ./database/database.sqlite
php artisan migrate

# 破棄
docker-compose down -v
```

## Docker Compose での運用
``` bash
# ビルド
docker-compose build

# 動作確認
docker-compose up -d

# コンテナ内に入る
docker-compose exec app bash

# セットアップ (コンテナ内で実行)
php artisan migrate

# 破棄
docker-compose down -v
```
