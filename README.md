# This repository to accomplish the Case study for Backend Developer in Token2049.

## Table of Contents
 - My Work Plan
 - Features
 - Prerequisites
 - Installation
 - API Endpoints
 - A Test steps for end-to-end process
   - Generate Session
   - Simulate Webhook
   - Monitor Queue
 - Deploy:
   - To DigitalOcean
 - Future Improvements
 - Troubleshooting

---------------------------
## My Work Plan:
1. Prepare an Isolated Dockerize Environment. -- Done
2. Prepare Database Migrations, Model, Controller and expose expected checkout API endpoint. -- Done
3. Prepare Webhook Receiver endpoint with mock payload and validation. -- Done
4. prepare health endpoint for app status. -- Done
5. Process asynchronous webhook requests. -- Done
6. Prepare console commands for Simulating the webhook requests and monitor queue -- DoONE
7. If I have time I will try to prepare PHPUnit test coverage with Red-Green-Refactor in mind. -- N/A
8. Documentation on how to run the project including future improvement possibilities. -- Done
9. Finally make the workflow to deploy in DigitalOcean. -- N/A
----------------------------
## Features:
1. **Crypto Checkout Simulation**: Simulates the process of creating payment sessions and tracking transactions, mocking a Coinbase Commerce integration. 
2. **Webhook-Driven Updates**: A webhook processing system to asynchronously update transaction statuses. 
3. **Clear Data Modeling**: Employs distinct database schemas and Enums for transaction and payment statuses, ensuring accurate tracking. 
4. **Asynchronous Processing**: Webhook handling is offloaded to background jobs for immediate response times and improved reliability. 
5. **Idempotency**: Designed to prevent duplicate processing of webhook events and payment records. 
6. **Retry Logic**: Webhook processing jobs include built-in retry mechanisms for transient failures. 
7. **Dynamic Webhook Simulation**: A powerful Artisan command to generate and send various mock Coinbase webhook payloads for thorough testing.
-----------------------------
### Prerequisites:
Ensure to have the following installed on the system where to run the project:
 - Copy the Repo: 
   - Make project directory  i.e. ```mkdir Token2049``` 
   - ```cd Token2049```
   - ```git clone https://github.com/sfaragy/token2049-Test```
 - Docker: For containerizing the application.
 - Docker Compose: For orchestrating the multi-container environment.
 - Make: A build automation tool (common on Linux/macOS; for Windows, consider WSL or Chocolatey).
   Run the following commands in the system terminal
  ##### Note: 
  To use the Make commands above in Linux OS please install:.
  ```
  sudo apt-get update
  sudo apt-get -y install make
  ```
-------------------------------------------------
## Installation:
### Step 1: Docker Container Management
Navigate to the project's root directory in the terminal.

 - Copy content from ```.env.example``` file and create another file ```.env``` in ```root/src/```
 - Build and initialize the project containers, install dependencies, and run migrations:
  ```
  make build
  make project_init
  ```
  - Or, Don't have the Make package installed to run the command then use basic docker and php artisan command: 

    - To build the containers: ```docker-compose -f docker-compose.yml build```
    - After docker container built, run the containers: ```docker-compose -f docker-compose.yml up --remove-orphans -d ```
    - Login inside the token2049_app container with the following command: ``` docker exec -it token2049_app_1 /bin/bash ```
    - Next run the commands respectively inside token2049_app container for project initialization:
      ```
        composer install
        php artisan storage:link
        php artisan key:generate
        php artisan migrate
        php artisan db:seed
        chown -R www-data:www-data storage bootstrap/cache
        chmod -R 775 storage bootstrap/cache
        php artisan queue:work
      ```
  #### Above process will accomplish:
  - Build Docker images (app, nginx, mysql, redis, queue_worker).
  - Start containers.
    - Install Composer dependencies.
    - Generate application key.
    - Migrate and Seed database.
    - Ensure initial file permissions. 
    - Start queue worker

- Start, Stop, and Restart the project containers:
  - To start: ``` make start```
  - To Stop: ```make stop```
  - To Restart: ```make restart```
  - To clear logs: ```make cler-log```
  - To Login to DB: ```db-login```

  or use direct command on project root:
  - To Start: ```docker-compose -f docker-compose.yml up --remove-orphans -d```
  - To Stop: ```docker-compose -f docker-compose.yml down```

------------------------------------------
## API Endpoints:
Once the project is initiated and running, API endpoints will be accessible via 
http://token2049.local.com/ or http://localhost/ (depending on Nginx/host configuration and host alias config).
1. **Create Checkout**: Simulates creating a payment checkout and returns a fake hosted payment URL.
2. **Webhook Receiver**: Accepts simulated webhook notifications from the payment processor", validates and processes transaction status updates asynchronously.
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
       "data": {
           "checkout_url": "https://fake.coinbase.com/pay/9d210758-2edf-4f8a-b66e-79c9376c5e4b",
           "transaction_id": "9d210758-2edf-4f8a-b66e-79c9376c5e4b"
       }
  }

- **Validation Error Response (HTTP 422 Unprocessable Entity):**

    ```json
  {
    "success": false,
    "message": "Invalid email or transaction amount",
    "errors": {
      "amount": ["The amount field must be a number."]
    }
  }

### 2. Webhook Receiver

- **Endpoint:** `POST /api/v1/webhook`
- **Description:** Description: This endpoint is designed to receive simulated Coinbase webhook notifications with transaction updates. 
It performs initial validation, including a placeholder for signature verification (commented for now), stores the raw payload, 
and then dispatches a background job (ProcessWebhookJob) for asynchronous processing of transaction status updates.

- **Sample Request Body:**

  ```json
  {
    "id": "evt_abc123",
    "type": "charge:confirmed",
    "created_at": "2025-06-16T12:34:56Z",
    "data": {
      "code": "ABCDE123",
      "metadata": {
        "transaction_id": "9d210758-2edf-4f8a-b66e-79c9376c5e4b",
        "email": "testemailtoken2049@example.com"
      },
      "timeline": [
        { "time": "2025-06-16T12:00:00Z", "status": "NEW" },
        { "time": "2025-06-16T12:10:00Z", "status": "PENDING" },
        { "time": "2025-06-16T12:34:56Z", "status": "COMPLETED" }
      ],
      "payments": [
        {
          "network": "bitcoin",
          "transaction_id": "0xabc",
          "status": "CONFIRMED",
          "value": { "amount": "0.002", "currency": "BTC" }
        }
      ]
    }
  }


- **Validation Rules (applied by WebhookController for initial receipt)**:

  - id: required, string. (Coinbase's unique webhook event ID, mocked)
  - type: required, string. (Coinbase's event type, e.g., charge:confirmed, mocked)
  - created_at: required, date. (Timestamp from Coinbase, mocked)
  - data.code: required, string. (Coinbase's charge code, mocked)
  - data.metadata.transaction_id: required, uuid. (Our internal transaction UUID)

  (**Note**: Deeper validation of the full payload content, such as specific statuses within timeline or payments arrays, occurs asynchronously within the ```ProcessWebhookJob```.)

- **Sample Response:**
  ```json
    {
      "success": true,
      "message": "Webhook request received and processing initiated."
    }


- **Validation Error Response (HTTP 422 Unprocessable Entity):**:

  ```json
    {
      "success": false,
      "message": "Invalid webhook payload structure",
      "errors": {
        "data.metadata.transaction_id": ["The data.metadata.transaction_id field is required."]
      }
    }

- Transaction Not Found Response (HTTP 404 Not Found, or 400 Bad Request if not caught by initial validation):
  (This response would typically be handled within the asynchronous ```ProcessWebhookJob```, 
but could be an explicit HTTP response if the ```transaction_id``` is validated synchronously before dispatching
the job.) i.e. provided transaction_id ```9d210758-2edf-4f8a-b66e-79c9376c5e44``` 
instead of ```9d210758-2edf-4f8a-b66e-79c9376c5e4b```

  ```json
  {
    "success":false,
    "message":"Invalid transaction Id",
    "transaction_id":"9d210758-2edf-4f8a-b66e-79c9376c5e44" 
  }


### 3. Health Check 

- **Endpoint:** `GET /api/v1/health`
- **Description:** This endpoint provides a basic health check for the application, returning its current status and a summary of transaction counts by status.

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
 ---------------------------- 
## A Test steps for end-to-end process:

 - **Step 1:** Create a checkout session: POST request to ```/api/v1/checkout ``` maybe with PostMan
    ```json
    {
      "amount": 100,
      "email": "testemailtoken2049@example.com"
    }
 - **Step 2:** Copy the value of ```"transaction_id":``` from response```e508924b-e7b9-43d1-820b-88e3d2c54f17```
 - **Step 3:** Simulate the Webhook request of Coinbase with the following command:
    - Login to the application docker container: ```make app-login``` or simple docker command ```docker exec -it token2049_app_1 /bin/bash```
    - Simulate webhook: Run this php artisan console command: ```php artisan simulate:webhook --transaction_id=e508924b-e7b9-43d1-820b-88e3d2c54f17 --email=testemailtoken2049@example.com --status=PENDING```
    **Note:** Please make sure the ```transaction_id``` same as previously collected ```transaction_id``` from checkout endpoint. 
 - **Step 4:** To monitor the queue is processing or not please follow the queue worker activity with the command make / inside docker:
    ```make queue-monitor``` or ```php artisan queue:monitor --restart --clear-failed --stats```
------------------------------------------------------
## Deploy:

### 1. Deploy to DigitalOcean:
- To Deploy in digital ocean please run the command from project root:
    ``` 
    sh deploy.sh   
    ```
  - **Note:** For now I just kept the tests enabled. 
  But if I get access to DigitalOcean then I can enable rest of the steps
## Future Improvements:
If given more time, the following enhancements would be prioritized to further stabilize, modernize, and extend the service:
 - **Robust Signature Verification:** Fully implement and test HMAC signature verification for all incoming webhooks using provider-specific secrets.
 - **Comprehensive Error Handling & Monitoring:** Implement centralized error tracking (e.g., Sentry which I normally use) and detailed metrics/dashboards for transaction states, webhook processing times, and job failures.
 - **Webhook Retry & Backoff Strategy:** Refine the job retry logic with exponential backoff and dead-letter queues that will leverage the system performance and scalability.
 - **Database Transactionality:** To Ensure all critical updates with ACID where involving multiple models are wrapped in database transactions for atomicity.
 - **Idempotency Handling:** Further enhance idempotency checks for webhooks using provider-specific unique identifiers (e.g., Coinbase's event id) to prevent duplicate processing even if the same webhook is sent multiple times.
 - **Dynamic Provider Handling:** Abstract the webhook processing to dynamically select the correct handler based on the incoming webhook's provider, allowing easy integration with other providers.
 - **PHPUnit Test Coverage:** Implement a comprehensive suite of unit, feature and integration tests (Red-Green-Refactor) for API endpoints, webhook processing logic, and services.
 - **Queued Event Handling:** Explore using Laravel events and listeners for certain parts of the webhook processing (e.g., PaymentConfirmed event triggering email notifications etc.).
 - **User Interface:** A simple dashboard to view transactions, their statuses, and webhook logs.
 - **Deployment Automation:** Full CI/CD pipeline for automated testing and deployment to DigitalOcean (or GCP, AWS etc.).
---------------------------------------
## Troubleshooting:
**Common Causes & Solutions:**

1. Nginx Not Running or Not Listening Correctly Internally:
    - **Action:** Log into the application container: ```docker compose exec token2049_app_1 /bin/bash```. 
   - **Check Nginx Status:** Run ``ps aux | grep nginx`` to confirm Nginx processes are running. 
   - **Check Listening Ports:** Run ```netstat -tulnp | grep 80``` (Maybe need to install net-tools first: ```apt update && apt install net-tools```) to confirm Nginx is listening on ```0.0.0.0:80``` or ```127.0.0.1:80```. 
   - **Internal Connectivity Test:** From within the container, try curl http://localhost. It should return application's HTML/JSON response. If it fails, Nginx isn't serving correctly internally. 
2. **APP_URL Mismatch (Less likely with localhost in single-container Nginx):**
 - APP_URL in .env to ensure it's http://localhost as expected for our Nginx setup. To restart Docker containers (``make restart``) after any changes in .env file.

----------------------------
Thank you for following the documentation. Hopefully it will help to run the project. Please feel free to contact for any concern or issues. 