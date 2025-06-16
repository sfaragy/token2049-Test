# Repo to accomplish the Case study for Backend Developer in Token2049.

## My Work Plan:
1. Prepare an Isolated Dockerize Environment.
2. Prepare Database Migrations, Model, Controller and expose expected checkout API endpoint.
3. Prepare Webhook Receiver endpoint with mock payload and validation.
4. prepare health endpoint for app status.
5. If I have time I will try to prepare PHPUnit test coverage with Red-Green-Refactor in mind. 
6. Documentation on how to run the project including future improvement possibilities.
7Finally make the workflow to deploy in DigitalOcean.

## How to Initiate project on Docker (Ubuntu or Linux users only): 
Run the following commands in your terminal
##### Note: Please make sure that the localhost machine has installed Make app.
```
sudo apt-get update
sudo apt-get -y install make
```
### Step1: Docker container management commands:  
- Build and initiate the project:
``` 
make build 
make project_init 

```
- Start and Stop the project with following commands:
```
make start
make stop
make restart
```
## How to Initiate project on Docker (Ubuntu or Linux users only):

### Step2: Ready to accept and process requests: 
```API URL: http://token2049.local.com/``` or ```http://localhost/```

API provides two main features as requested:

1. **Create Checkout**: Simulates creating a payment checkout and returns a fake hosted payment URL.  
2. **Webhook Receiver**: Accepts simulated webhook notifications from the "payment processor", validates and processes transaction status updates asynchronously.
3. **App Status / Health Check**: This endpoint will generate current status of the transactions classified with it\'s status
### 1. Create Checkout Session

- **Endpoint:** `POST /api/v1/checkout`  
- **Description:** Simulate creating a payment checkout. Returns a fake hosted payment URL.  

- **Sample Request Body:**

  ```json
  {
    "amount": 100.00,
    "email": "testemailtoken2049@example.com"
  }
  
- Validation Rules:
    - amount: required, numeric, minimum value 1.
    - email: required, valid email address.

- **Sample Response:**
    ```json
  {
      "success": true,
      "checkout_url": "https://fake.coinbase.com/pay/7a06e022-f86b-44f5-aa15-e5a7f72b1274"
  }

- ** Validation Error Response:**

    ```json
  {
     "success":false,
     "message":"Invalid email or transaction amount",
     "errors": {
         "amount":["The amount field must be a number."] 
     }
  }

### 2. Webhook Receiver

- **Endpoint:** `POST /api/v1/webhook`
- **Description:** Receives simulated webhook notifications with transaction updates. Validates and processes updates asynchronously. The authenticity of the request is invalid for now (doesn't have secret, HMAC signature, etc.)

- **Sample Request Body:**

  ```json
    {
    "transaction_id": "dcc59d3f-5f7b-458f-8580-28083566973b",
    "status": "confirmed",
    "email": "testemailtoken2049@example.com"
    }


- Validation Rules:
    - transaction_id: required, valid UUID format.
    - status: required, enum of valid statuses (e.g. pending, confirmed, failed).
    - email: required valid email.

- **Sample Response:**
    ```json
  {
    "success":true,
    "skipped":false,
    "message":"Transaction updated!"
  }


- Validation Error Response:

    ```json
  {
    "success":false,
    "message":"Invalid input data",
    "errors": {
       "status":["The selected status is invalid."]
     }
  }

- Transaction Not Found Response:

  ```json
  {
    "success":false,
    "message":"Invalid transaction Id",
    "transaction_id":"7a06e022-f86b-44f5-aa15-e5a7f72b1275"
  }


### 3. Health Check 

- **Endpoint:** `GET /api/v1/health`
- **Description:** This endpoint will generate current status of the transaction classified with it\'s status

- **Sample Response:**
    ```json
  {
    "status": "ok",
    "timestamp": "2025-06-16 13:13:32",
    "env": "local",
     "stats": {
       "pending": 6,
       "completed": 1,
       "failed": 0
     }
  }
  
