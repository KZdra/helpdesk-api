# HelpDesk-Api

API BUAT HELPDESK TICKETING

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

#### Get all TICKETS

```http
  GET /api/auth/tickets
```

| Parameter | Type     | Description   |
| :-------- | :------- | :------------ |
| `token`   | `string` | **Required**. |

#### Get SINGLE TICKET

```http
  GET /api/auth/tickets/{ticket_number}
```

| Parameter       | Type     | Description                                 |
| :-------------- | :------- | :------------------------------------------ |
| `ticket_number` | `string` | **Required**. Ticketnumber of item to fetch |
| `token`         | `string` | **Required**.                               |

#### Add Ticket

```http
  POST /api/auth/tickets
```

| Parameter | Type     | Description   |
| :-------- | :------- | :------------ |
| `token`   | `string` | **Required**. |

| Body    | Type     | Description   |
| :------ | :------- | :------------ |
| `issue` | `string` | **Required**. |

#### Edit Ticket

```http
  PUT /api/auth/tickets/{ticket_number}
```

| Parameter | Type     | Description   |
| :-------- | :------- | :------------ |
| `token`   | `string` | **Required**. |

| Body     | Type     | Description                           |
| :------- | :------- | :------------------------------------ |
| `issue`  | `string` | **Required**.                         |
| `status` | `string` | **Required**. open,in_progress,closed |

#### Edit Ticket STATUS

```http
  PUT /api/auth/tickets/{ticket_number}/status
```

| Parameter | Type     | Description   |
| :-------- | :------- | :------------ |
| `token`   | `string` | **Required**. |

| Body     | Type     | Description                           |
| :------- | :------- | :------------------------------------ |
| `status` | `string` | **Required**. open,in_progress,closed |

### SUDAH TERMASUK AUTH JWT TOKEN
