# /takeback

Takes back a move.

## `action`

The action to take as per these options.

- `propose`
- `decline`
- `accept`

---

## Usage

### Propose a take back

```js
ws.send('/takeback propose');
```

```text
{
  "/takeback": {
    "action": "propose"
   }
}
```

### Decline a take back

```js
ws.send('/takeback decline');
```

```text
{
  "/takeback": {
    "action": "decline"
   }
}
```

### Accept a take back

```js
ws.send('/takeback accept');
```

```text
{
  "/takeback": {
    "action": "accept"
   }
}
```
