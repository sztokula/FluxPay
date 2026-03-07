# Mini Stripe-like Payment Platform (Laravel + SQLite)

## 1. Purpose

This repository contains a full implementation guide for building a Stripe-like payment platform simulation using Laravel and SQLite. The project demonstrates payment lifecycle architecture, subscription billing, webhook delivery, retry logic, and a minimal storefront checkout flow.

The goal is to provide a developer-focused environment that behaves similarly to a real payment processor while remaining fully local and safe for experimentation.

This project intentionally avoids real payment gateways. Instead, it simulates payment authorization, failures, retries, and asynchronous events using deterministic test cards.

The system is designed as a modular monolith so it can later evolve into a microservice architecture if needed.

---

## 2. Tech Stack

Framework:
Laravel (latest stable version)

Database:
SQLite

Queue:
Laravel database queue

Scheduler:
Laravel Scheduler

Authentication:
Laravel Sanctum

Frontend:
Blade templates + Tailwind CSS

Testing:
Pest or PHPUnit

Runtime:
PHP 8.3+

---

## 3. High Level System Components

The application contains the following logical domains:

Customers
Payments
Subscriptions
Invoices
Webhooks
Fraud detection
Ledger
Payouts
Event logging

Directory structure example:

app/
  Domain/
    Customers/
    Payments/
    Subscriptions/
    Invoices/
    Webhooks/
    Fraud/
    Ledger/
  Actions/
  DTO/
  Enums/
  Jobs/
  Services/

Controllers remain thin and delegate logic to domain actions.

---

## 5. Demo Storefront

The demo application includes a simple storefront to demonstrate payment flow.

Routes:

/
/products
/product/{id}
/checkout/{product}
/payment/{intent}
/payment/success
/payment/failed

Example Product model

class Product extends Model
{
    protected $fillable = ['name','description','price'];
}

Seeder example

Product::create([
'name' => 'Developer T-Shirt',
'description' => 'Simple cotton T-shirt for developers',
'price' => 2900
]);

Prices are stored in cents.

---

## 6. Checkout Flow

1 User opens product page

2 User clicks "Buy Now"

3 Application creates PaymentIntent

4 User enters test card

5 Payment is confirmed

6 PaymentIntent transitions to succeeded or failed

7 Webhook event generated

8 Order finalized

---

## 7. Payment Intent Model

Example model

class PaymentIntent extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'idempotency_key'
    ];
}

Statuses:

requires_payment_method
requires_confirmation
processing
succeeded
failed
canceled
requires_action

---

## 8. Payment Processing Simulation

The application contains a PaymentSimulator service responsible for deterministic outcomes.

Example logic

class PaymentSimulator
{
    public function process(string $cardNumber): string
    {
        return match ($cardNumber) {
            '4242424242424242' => 'success',
            '4000000000000002' => 'insufficient_funds',
            '4000000000009995' => 'temporary_failure',
            '4000000000003063' => 'requires_action',
            default => 'failure'
        };
    }
}

---

## 9. Payment Retry Policy

Retryable errors should schedule background attempts.

Example schedule

Attempt 1: immediately
Attempt 2: +5 minutes
Attempt 3: +30 minutes
Attempt 4: +6 hours
Attempt 5: +24 hours

After maximum retries the payment is marked failed.

---

## 10. Subscriptions

Subscriptions consist of

Plan
Price
Subscription
Invoice

Example Subscription model

class Subscription extends Model
{
    protected $fillable = [
        'customer_id',
        'price_id',
        'status',
        'current_period_start',
        'current_period_end'
    ];
}

Statuses

trialing
active
past_due
unpaid
canceled

---

## 11. Invoice Generation

Invoices are created when

A subscription is started
A billing cycle renews
Manual invoice is generated

Invoice statuses

draft
open
paid
void
uncollectible

---

## 12. Webhooks

Webhook endpoints allow external systems to subscribe to events.

Events supported

payment_intent.created
payment_intent.succeeded
payment_intent.failed
invoice.created
invoice.paid
subscription.created
subscription.canceled
payout.created
payout.failed

Delivery system uses queued jobs.

If endpoint returns non-2xx response, retry logic triggers.

---

## 13. Fraud Scoring

Fraud scoring uses simple rules

Too many attempts in short window
High value transaction without history
Repeated failures
Blacklisted test cards

Fraud results

allow
review
block

---

## 14. Ledger System

Ledger tracks financial movement.

Entry types

charge
refund
payout
fee
adjustment

Each entry records debit or credit movement.

---

## 15. Payout System

Payouts simulate transfers to merchants.

Statuses

pending
processing
paid
failed

Ledger entries ensure balance integrity.

---

## 16. Queue Workers

Run worker

php artisan queue:work

Scheduler

php artisan schedule:work

---

## 17. Example API

Create customer

POST /api/customers

Create payment intent

POST /api/payment-intents

Confirm payment

POST /api/payment-intents/{id}/confirm

Create subscription

POST /api/subscriptions

Register webhook

POST /api/webhooks

---

## 18. Idempotency

All mutating API endpoints support idempotency keys.

Example header

Idempotency-Key: 8c2b4f

If request is repeated with same key the previous result is returned.

---

## 19. Observability

Every important action produces an event record stored in database.

This allows debugging payment lifecycle and webhook deliveries.

---

## 20. Future Improvements

Possible upgrades

PostgreSQL instead of SQLite
Redis queues
Horizon monitoring
Event sourcing
Separate microservices

---

## 21. Developer Notes

The project prioritizes clarity of payment lifecycle rather than UI polish.

Code comments should explain reasoning behind payment state transitions and retry logic.

The repository should remain understandable for backend developers exploring payment infrastructure.

