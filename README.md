# sample_dockerized_laravel6
Laravel6 を Docker 化するサンプルです。

## Docker 化の実行
```bash
# ビルド
docker build -t sample_dockerized_laravel6 .

# 動作確認
docker container run -d --rm -p 80:80 --name sample_dockerized_laravel6 sample_dockerized_laravel6:latest

# コンテナ内に入る
docker container exec -it sample_dockerized_laravel6 bash

# 停止
docker container stop sample_dockerized_larave6
```
