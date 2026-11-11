# AWS MLA-C01 Reviewer

A free, public practice site for the **AWS Certified Machine Learning Engineer – Associate (MLA-C01)** exam,
built with **Laravel + Blade**. 137 multiple-choice questions in 25 topics. Every answer shows an explanation for
the correct choice *and* each distractor. There are no accounts and no database; learners' progress lives in their
own browser (localStorage).

```
overlay/                       files applied on top of a fresh Laravel app
  routes/web.php               / , /quiz , /about , /question-bank
  app/Http/Controllers/QuizController.php
  resources/views/             layout + home + quiz + about (Blade)
  resources/data/questions.json  <- the question bank (edit this)
  public/css/app.css, public/js/{quiz,progress}.js
  tests/Feature/QuizPagesTest.php
setup.sh                       creates the Laravel app and applies overlay/
```

## Requirements

- PHP 8.2+ and [Composer](https://getcomposer.org)
- Written for the current Laravel skeleton (11/12). It uses no database, auth or queues.
- Note: the Laravel-specific parts (routes, controller, Blade views) could not be run inside a Laravel install
  where this was built. Run `php artisan test` right after `setup.sh` to confirm they work on your machine.

## Quick start

macOS / Linux / WSL / Git Bash:

```bash
./setup.sh                    # creates ./mla-c01-reviewer-app
cd mla-c01-reviewer-app
php artisan test
php artisan serve             # http://127.0.0.1:8000
```

Windows (PowerShell), if you'd rather not use Git Bash:

```powershell
composer create-project laravel/laravel mla-c01-reviewer-app
Copy-Item -Recurse -Force overlay\* mla-c01-reviewer-app\
cd mla-c01-reviewer-app
# in .env set: SESSION_DRIVER=file  CACHE_STORE=file  QUEUE_CONNECTION=sync
php artisan serve
```

## Editing questions

Questions live in `resources/data/questions.json`. Each entry:

```json
{
  "id": 1,
  "topic": "Amazon S3",
  "topic_slug": "amazon-s3",
  "question": "…",
  "correct": 2,
  "options": [ { "text": "…", "why": "…" }, … exactly 4 … ],
  "takeaway": "…"
}
```

- `correct` is the 0-based index into `options`; answer order is shuffled for learners.
- Keep `id` unique and stable. Learners' "missed question" lists are keyed by it.
- New topics appear automatically (they're built from `topic` / `topic_slug`).
- The bank is cached for an hour. After editing on a live server run `php artisan cache:clear`.
- `php artisan test` fails if a question doesn't have 4 options and a valid `correct` index.

## Deploying

Always run in production mode: set `APP_ENV=production` and `APP_DEBUG=false` in `.env`, then:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate        # only if APP_KEY is empty
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Re-run the three `:cache` commands after each deploy.

### Option A: VPS (nginx + PHP-FPM)

Point the web root at the app's **`public/`** folder (never the project root):

```nginx
server {
    listen 80;
    server_name reviewer.example.com;
    root /var/www/mla-c01-reviewer-app/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;   # match your PHP version
    }
    location ~ /\.(?!well-known).* { deny all; }
}
```

Make `storage/` and `bootstrap/cache/` writable by the web user, then add HTTPS with Let's Encrypt
(`certbot --nginx`) or put the site behind Cloudflare.

### Option B: shared hosting (cPanel etc.)

1. Run `composer install --no-dev --optimize-autoloader` on your computer so the `vendor/` folder exists.
2. Upload the whole project. If you can, set the domain's document root to `public/`.
   If you can't, upload the project **outside** `public_html`, and copy the contents of `public/` into
   `public_html`, then edit the `../` paths in `public_html/index.php` (maintenance file, `vendor/autoload.php`,
   `bootstrap/app.php`) so they point at the project folder.
3. Make sure `storage/` and `bootstrap/cache/` are writable (permission 775).

### Option C: managed PHP platforms

Laravel Forge, Ploi, Render, Railway, Fly.io etc. work too. There's nothing special to configure: no database,
no queue worker, no scheduler. Build command `composer install --no-dev --optimize-autoloader`, web root `public/`.

## Privacy

The site stores nothing server-side about learners. Progress (scores per topic, missed question ids) is kept in
`localStorage` under the key `mla-c01-reviewer:v1`. There is no analytics code. Laravel's standard web
middleware may still set its default session/XSRF cookies, which this site doesn't use for anything.

## Content & credits

**This reviewer is based on the AWS Machine Learning Engineer Associate (MLA-C01) study notes by
Christian Greciano.** Full credit for the source material goes to him:

> [AWS Machine Learning Engineer Associate (MLA-C01): study notes](https://psychedelic-cuticle-e74.notion.site/AWS-Machine-Learning-Engineer-Associate-MLA-C01-19686c7395e780e1bab0eac37d0401a0)

The multiple-choice questions in this project were written from those notes. They are a study aid and are not a
copy of the notes. Please read the original pages for the full explanations.

### Source pages

**Data ingestion & storage**

- [Amazon SageMaker Introduction](https://psychedelic-cuticle-e74.notion.site/Amazon-SageMaker-Introduction-19686c7395e78136b5dbdb0b045f01b5)
- [Data Ingestion and Storage](https://psychedelic-cuticle-e74.notion.site/Data-Ingestion-and-Storage-19686c7395e781b5aed1e541d72752ad)
- [Amazon S3 (Simple Storage Service) 101](https://psychedelic-cuticle-e74.notion.site/Amazon-S3-Simple-Storage-Service-101-27e12f4a7f5045c3962d335903f0f1d8)
- [S3 Security: Bucket Policies & ACLs](https://psychedelic-cuticle-e74.notion.site/S3-Security-Bucket-Policies-ACLs-ab75e9fd11c549308eb9c85b59020b11)
- [S3 Static Website Hosting & Billing](https://psychedelic-cuticle-e74.notion.site/S3-Static-Website-Hosting-Billing-324562aaf57644e2b51c49855ef121b8)
- [S3 Object Versioning & MFA Delete](https://psychedelic-cuticle-e74.notion.site/S3-Object-Versioning-MFA-Delete-84c6c5370546401aaf5af8990ce48e36)
- [S3 Performance Optimization](https://psychedelic-cuticle-e74.notion.site/S3-Performance-Optimization-6c97564a263647cba44946299581cae1)

**Feature engineering, data transformation & data integrity**

- [Feature Engineering](https://psychedelic-cuticle-e74.notion.site/Feature-Engineering-19686c7395e781f08273da4d0c8f90fd)
- [Apache Hadoop and Apache Spark](https://psychedelic-cuticle-e74.notion.site/Apache-Hadoop-and-Apache-Spark-19686c7395e7814bb89de8fd9dd51e66)
- [Amazon Elastic MapReduce (EMR)](https://psychedelic-cuticle-e74.notion.site/Amazon-Elastic-MapReduce-EMR-19686c7395e781a8ad02e791d1efd5f0)
- [LAB: TF-IDF on EMR with Spark](https://psychedelic-cuticle-e74.notion.site/LAB-TF-IDF-on-EMR-with-Spark-19686c7395e781f2b765f3a157c243f8)
- [AWS Glue (MLA-C01)](https://psychedelic-cuticle-e74.notion.site/AWS-Glue-MLA-C01-19686c7395e781e98930e629bd3d181c)
- [AWS Lake Formation](https://psychedelic-cuticle-e74.notion.site/AWS-Lake-Formation-19686c7395e781dcb0b5e5643da0f24e)
- [Amazon Athena (MLA-C01)](https://psychedelic-cuticle-e74.notion.site/Amazon-Athena-MLA-C01-19686c7395e7810d8b93eeceda012c6a)
- [Amazon QuickSight](https://psychedelic-cuticle-e74.notion.site/Amazon-QuickSight-19686c7395e781268237e6db5f873cb8)
- [SageMaker Data Processing](https://psychedelic-cuticle-e74.notion.site/SageMaker-Data-Processing-19686c7395e781abad3cef0b146084ea)

**Deep learning & SageMaker modelling**

- [Deep Learning (DL) & Neural Networks (NNs)](https://psychedelic-cuticle-e74.notion.site/Deep-Learning-DL-Neural-Networks-NNs-19686c7395e781b2a251c44866d7a22a)
- [SageMaker: Built-in Algorithms](https://psychedelic-cuticle-e74.notion.site/SageMaker-Built-in-Algorithms-19686c7395e7814fa0ceda3ac7370ed4)
- [SageMaker: Model Training, Tuning, and Evaluation](https://psychedelic-cuticle-e74.notion.site/SageMaker-Model-Training-Tuning-and-Evaluation-19686c7395e7819bae4fdda19f83d171)

**AWS-managed AI services**

- [Amazon Comprehend](https://psychedelic-cuticle-e74.notion.site/Amazon-Comprehend-10e86c7395e78031ac0cf65af784487f)
- [Amazon Translate](https://psychedelic-cuticle-e74.notion.site/Amazon-Translate-10f86c7395e780d49767d4ba57897aa8)
- [Amazon Textract](https://psychedelic-cuticle-e74.notion.site/Amazon-Textract-10f86c7395e780a6b8b5d53ecbcb628c)
- [Amazon Rekognition](https://psychedelic-cuticle-e74.notion.site/Amazon-Rekognition-10f86c7395e78029baa4ccace1384148)
- [Amazon Kendra](https://psychedelic-cuticle-e74.notion.site/Amazon-Kendra-10f86c7395e780248fe7f1136ec872f8)
- [Amazon Lex](https://psychedelic-cuticle-e74.notion.site/Amazon-Lex-10f86c7395e780799a9df705e0a70574)
- [Amazon Polly](https://psychedelic-cuticle-e74.notion.site/Amazon-Polly-10f86c7395e78098ba4fe2a91bfe3c00)
- [Amazon Transcribe](https://psychedelic-cuticle-e74.notion.site/Amazon-Transcribe-10f86c7395e7802f9ea7c067c6987c70)
- [Amazon Personalize](https://psychedelic-cuticle-e74.notion.site/Amazon-Personalize-21486c7395e780fbacd8f04bc0e240f1)
- [Amazon Fraud Detector](https://psychedelic-cuticle-e74.notion.site/Amazon-Fraud-Detector-10f86c7395e780ff9b81d9ad07d64654)
- [Amazon Lookout](https://psychedelic-cuticle-e74.notion.site/Amazon-Lookout-21686c7395e780a29562c174d9c3e948)
- [Other AI Services in AWS](https://psychedelic-cuticle-e74.notion.site/Other-AI-Services-in-AWS-10f86c7395e780318a61ca432e72c7bc)

**Generative AI**

- [Transformers and LLMs](https://psychedelic-cuticle-e74.notion.site/Transformers-and-LLMs-19686c7395e7813b898ec78d29f4f6d1)
- [GenAI in AWS](https://psychedelic-cuticle-e74.notion.site/GenAI-in-AWS-19686c7395e781579e8ceb3b9dcacaf8)
- [LABS: Transformers and FMs in SageMaker](https://psychedelic-cuticle-e74.notion.site/LABS-Transformers-and-FMs-in-SageMaker-19686c7395e781669eefc8a217bf542b)
- [GenAI apps in Amazon Bedrock](https://psychedelic-cuticle-e74.notion.site/GenAI-apps-in-Amazon-Bedrock-19686c7395e7816286c1fbcb380e78ec)

**DevOps / MLOps**

- [AWS Products for DevOps/MLOps](https://psychedelic-cuticle-e74.notion.site/AWS-Products-for-DevOps-MLOps-19686c7395e781bea02bfc18126172c1)
- [Intro to ECS (from SAA-C03 notes)](https://psychedelic-cuticle-e74.notion.site/Amazon-ECS-Elastic-Container-Service-101-c8d2bc1c48344e29822977876c7219f9)
- [Intro to EKS (from SAA-C03 notes)](https://psychedelic-cuticle-e74.notion.site/Amazon-EKS-Elastic-Kubernetes-Service-101-8b81ca32381c4da1b4a14fe007d51ccf)
- [Intro to CloudFormation (from SAA-C03 notes)](https://psychedelic-cuticle-e74.notion.site/AWS-CloudFormation-CFN-101-80186a5de60d46faa77ba7b762bcef62)
- [Intro to EventBridge (from SAA-C03 notes)](https://psychedelic-cuticle-e74.notion.site/Amazon-EventBridge-c57081d57f3f4c3381fe1ceb87f2d85a)
- [Intro to Step Functions (from SAA-C03 notes)](https://psychedelic-cuticle-e74.notion.site/AWS-Step-Functions-101-7d04ae82e8a94fd499aaa5458c912f08)

### Additional sources (generative-AI / MLA-C02 topics)

The questions on Bedrock Guardrails, RAG & Knowledge Bases, FM customization & selection, Bedrock inference & cost,
Agents & AgentCore and FM evaluation were written from AWS's public documentation and FAQs:
Bedrock Guardrails docs, Knowledge Bases chunking, Bedrock FAQs, AgentCore FAQs, Bedrock Evaluations and
Cross-Region inference. See the [Amazon Bedrock documentation](https://docs.aws.amazon.com/bedrock/) and
[Bedrock FAQs](https://aws.amazon.com/bedrock/faqs/).

### Notes

Confirm you have permission to republish material derived from the notes before launching publicly, and keep the
credit on the About page and in the site footer. The site is an unofficial study aid; it is not affiliated with
AWS. The questions may contain errors or go out of date, so always check the official exam guide.
