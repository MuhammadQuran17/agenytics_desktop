# Dev Documentation of Ai agent starter kit

## IMPORTANT

- If you will move queue processor from db to redis, make sure that UserJobLimiter is also fixed, because it uses db to check if user has too many jobs in queue.

## Frontend

- in resources/js/store we have used Pinia Store Management
- We use ready to use Laravel's Vue js, Starter kit, that uses `shadcn-ui`. Global reusable components of shadcn-ui is located in `js/components`.
- in `js/components_project` we have project components. It is like Services. First if we need new page, we create new file in js/pages, then we create in `componets_project` new components to isolate some logic to aquire SPR (single principle responsibility)
- If you want to understand/modify huge components like ChatBot.vue, it's recommended first to fold entire file and you will see hint comments like [START] and [END]. So open only neccessary parts.

## Backend

- We are using Controllers and Services
- in AiCHat we have used Strategy Pattern for Responses. Registered in ServiceProvider. In Factories we have strategy resolving logic
- We have integration with N8N service in `app/Services/N8N/N8nAiAgent.php`. 
- in .env if you make true `IS_FAKE_RESPONSES_ENABLED` parameter, then you will get mock/test response from N8N, and test entire chat functionality.
- For Tests we have used pest framework.
- We use Mailpit for email local testing just call localhost:8025 and UI will be available.

## DB

1. chat_histories save user input in `user_input` column and all unneccessary columns are empty. It save `ai response` in all columns except `user_input`. 

## Feedbacks

1. feedback is the same FeatureRequest. The same entity