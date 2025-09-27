# Humanitarian Aid Platform - API Quick Reference

- Base URL: `http://127.0.0.1:8000`
- Prefix: all endpoints are under `/api`
- Auth: Bearer token (Sanctum)
  - Header: `Authorization: Bearer <token>`
  - Always send: `Accept: application/json`

## Auth
- POST `/api/register`
- POST `وايض`
- POST `/api/logout` (auth)
- GET `/api/user` (auth)

Example: login
```bash
curl -X POST \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@humanitarian.aid","password":"password"}' \
  http://127.0.0.1:8000/api/login
```

## Users (auth)
- GET `/api/users` (admin sees all; others see self)
- POST `/api/users` (admin)
- GET `/api/users/{user}`
- PUT/PATCH `/api/users/{user}`
- DELETE `/api/users/{user}`
- GET `/api/users/role/{role}`
- GET `/api/users/beneficiaries`
- GET `/api/users/volunteers`

Create (admin):
```bash
curl -X POST -H "Accept: application/json" -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"name":"Vol One","email":"vol1@example.com","password":"password","role":"volunteer"}' \
  http://127.0.0.1:8000/api/users
```

## Aid Requests (auth)
- GET `/api/aid-requests`
- POST `/api/aid-requests` { type, description, document? (file) }
- GET `/api/aid-requests/{aid_request}`
- PUT/PATCH `/api/aid-requests/{aid_request}` { status: pending|approved|denied }
- DELETE `/api/aid-requests/{aid_request}`

Create (JSON):
```bash
curl -X POST -H "Accept: application/json" -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"type":"food","description":"Need food"}' \
  http://127.0.0.1:8000/api/aid-requests
```

## Donations (auth)
- GET `/api/donations`
- POST `/api/donations` { donor_name, type, quantity, status? }
- GET `/api/donations/{donation}`
- PUT/PATCH `/api/donations/{donation}`
- DELETE `/api/donations/{donation}`
- POST `/api/donations/{donation}/approve`
- POST `/api/donations/{donation}/distribute`

## Distributions (auth)
- GET `/api/distributions`
- POST `/api/distributions` { volunteer_id, beneficiary_id, donation_id, delivery_status? }
- GET `/api/distributions/{distribution}`
- PUT/PATCH `/api/distributions/{distribution}` { delivery_status?, proof_file? (file) }
- DELETE `/api/distributions/{distribution}`
- POST `/api/distributions/{distribution}/status/{status}` (assigned|in_progress|delivered)
- GET `/api/volunteer/distributions`

Create:
```bash
curl -X POST -H "Accept: application/json" -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"volunteer_id":4,"beneficiary_id":27,"donation_id":61}' \
  http://127.0.0.1:8000/api/distributions
```

## Notifications (auth)
- GET `/api/notifications`
- POST `/api/notifications` { user_id, message, type }
- GET `/api/notifications/{notification}`
- PUT/PATCH `/api/notifications/{notification}` { status: unread|read }
- DELETE `/api/notifications/{notification}`
- POST `/api/notifications/mark-all-read`
- GET `/api/notifications/unread-count`

## Dashboard (auth)
- GET `/api/dashboard/stats`
- GET `/api/dashboard/charts`
- GET `/api/dashboard/activity`
- GET `/api/dashboard/user-stats`

## Uploads (auth)
- POST `/api/upload-document` (multipart)
  - Fields: `file` (<=10MB), `type` in [document|proof]

Example (multipart):
```bash
curl -X POST -H "Accept: application/json" -H "Authorization: Bearer <TOKEN>" \
  -F "file=@/path/to/file.pdf" -F "type=document" \
  http://127.0.0.1:8000/api/upload-document
```

## Generated interactive docs
- HTML docs: `http://127.0.0.1:8000/docs` (also via `http://127.0.0.1:8000/api`)
- Postman: `http://127.0.0.1:8000/docs.postman`
- OpenAPI: `http://127.0.0.1:8000/docs.openapi`
