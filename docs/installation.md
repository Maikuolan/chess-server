# Installation

## Requirements

- PHP >= 8.1
- Stockfish >= 15.1

## Setup

Clone the `chesslablab/chess-server` repo into your projects folder. Then `cd` the `chess-server` directory and install the Composer dependencies:

```txt
composer install
```

Create an `.env` file:

```txt
cp .env.example .env
```

Finally, you may want to add the following entry to your `/etc/hosts` file if running the chess server on your localhost along with the [ChesslaBlab website](https://github.com/chesslablab/website) as per the `WEBSOCKET_HOST` variable defined in the [assets/env.example.js](https://github.com/chesslablab/website/blob/main/assets/env.example.js) file.

```txt
127.0.0.1       async.chesslablab.org
```

## Run the Chess Server

PHP Chess Server uses Workerman WebSockets.

| Script | Description |
| ------ | ----------- |
| [cli/workerman/wss.php](https://github.com/chesslablab/chess-server/blob/main/cli/workerman/wss.php) | Secure WebSocket. |

Alternatively, it can use Ratchet WebSockets.

| Script | Description |
| ------ | ----------- |
| [cli/ratchet/wss.php](https://github.com/chesslablab/chess-server/blob/main/cli/ratchet/wss.php) | Secure WebSocket. |


Before starting the secure WebSocket server for the first time, make sure to have created the `fullchain.pem` and `privkey.pem` files in the `ssl` folder.

```txt
php cli/workerman/wss.php start -d
```

## Run the Chess Server on a Docker Container

The chess server can also run on a Docker container.

```txt
docker compose -f docker-compose.wss.yml up -d
```
