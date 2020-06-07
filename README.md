# Description
This project is a YouTube viewer built using Angular for the frontend and Laravel for the backend. It is designed to be 
hosted on AWS using their serverless functionality. It uses Lambda to host the PHP code, DynamoDB as a database, 
API Gateway to communicate between the frontend and backend and S3 to host the frontend files.

Features
- Search for YouTube videos
- First result is automatically played
- Additional results are displayed and can be selected
- Search history is stored if user is logged in

# Frontend
### Description
The frontend is based on Angular. To compile the frontend, NPM is needed.

### Compilation Process
- In frontend tamid-youtube folder, run ``npm install``
- Create an ``environment.ts`` file and fill out appropriately
- Run ``npm run build`` to generate the compiled application in the ``dist`` folder
- The generate source can be deployed to S3 or to an any other server such as nginx

# Backend
### Description
The backend is based on Laravel and is configured to use AWS Lambda, API Gateway and DynamoDB so that it can run as a serverless application.
To setup the backend, composer and npm are needed

### Setup process
- Setup DynamoDB
  - In the DynamoDB AWS console, create the following tables: 
  ``failed_jobs, oauth_access_tokens, oauth_clients, oauth_refresh_tokens, search_history, users``
  - Each table should have a string key called id. search_history should also have a string sort key called search_date
- Create an AWS IAM user for serverless with the ``AdministratorAccess`` permission
  - This will only be used for deploying the application
  - Generate and save access keys for this user for the serverless deploy
- Create an AWS IAM user for the application with the following roles:
  - AWSLambdaFullAccess, AmazonDynamoDBFullAccess, AWSLambdaRole
  - This user's access keys will be used in ``.env``
- Install serverless: ``npm install -g serverless``
- Create ``serverless.yml`` using ``serverless.example.yml``
- Create ``.env`` using ``.env.example``
- Run ``composer install``
- Generate app key using ``php artisan key:generate``
- Generate OAuth key using ``php artisan passport:keys``
- Run ``php artisan passport:client --password --name 'TAMID_YT Personal Access Client'``
  - This will throw an error related to DynamoDB, but it can be ignored
  - Get the generated ``id`` and ``secret`` from the ``oauth_clients`` table in DynamoDB. 
  - This will be used in the frontend ``environment.ts`` file 
- Run ``php artisan config:cache``
- Run ``php artisan config:clear``
