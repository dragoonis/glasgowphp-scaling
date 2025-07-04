# GlasgowPHP CQRS API - Postman Collection

This Postman collection provides a complete set of API endpoints for testing the CQRS (Command Query Responsibility Segregation) implementation with Symfony Messenger and Redis projections.

## 🚀 Quick Start

### 1. Import the Collection
1. Open Postman
2. Click "Import" button
3. Select the `postman_collection.json` file
4. The collection will be imported with all endpoints organized by entity

### 2. Set Up Environment Variables
The collection uses the following variables that you can customize:

| Variable | Default Value | Description |
|----------|---------------|-------------|
| `base_url` | `http://localhost:8088` | Base URL for the API |
| `product_id` | `1` | Default product ID for testing |
| `customer_id` | `1` | Default customer ID for testing |
| `order_id` | `1` | Default order ID for testing |

### 3. Test the API
Start with the "System > Health Check" endpoint to verify the API is running.

## 📋 Available Endpoints

### Products
- **GET** `/en/products` - List all products (Redis projection read)
- **GET** `/en/products/{id}` - Get product by ID (Redis projection read)
- **POST** `/en/products` - Create new product (Database write + Redis update)
- **PUT** `/en/products/{id}` - Update product (Database write + Redis update)
- **DELETE** `/en/products/{id}` - Delete product (Database write + Redis cleanup)

### Customers
- **GET** `/en/customers` - List all customers (Redis projection read)
- **GET** `/en/customers/{id}` - Get customer by ID (Redis projection read)
- **POST** `/en/customers` - Create new customer (Database write + Redis update)
- **DELETE** `/en/customers/{id}` - Delete customer (Database write + Redis cleanup)

### Orders
- **GET** `/en/orders` - List all orders (Redis projection read)
- **GET** `/en/orders/{id}` - Get order by ID (Redis projection read)
- **POST** `/en/orders` - Create new order (Database write + Redis update)
- **DELETE** `/en/orders/{id}` - Delete order (Database write + Redis cleanup)

## 🔧 Testing Workflow

### 1. Create Test Data
1. **Create a Customer**: Use "Create Customer" with sample data
2. **Create Products**: Use "Create Product" to add some products
3. **Create Orders**: Use "Create Order" with the customer ID and product data

### 2. Test Read Operations
1. **List All**: Test the list endpoints to see all entities
2. **Get by ID**: Use specific IDs to retrieve individual entities
3. **Verify Performance**: Notice the fast response times from Redis projections

### 3. Test Write Operations
1. **Create**: Add new entities and verify they appear in lists
2. **Delete**: Remove entities and verify they're removed from projections
3. **Check Consistency**: Ensure database and Redis projections stay in sync

## 📝 Sample Request Data

### Create Product
```json
{
  "name": "Sample Product",
  "description": "This is a sample product description",
  "price": "99.99",
  "created_at": "2024-01-01T00:00:00+00:00"
}
```

### Create Customer
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "address": "123 Main Street",
  "city": "Glasgow",
  "postal_code": "G1 1AA",
  "country": "United Kingdom",
  "created_at": "2024-01-01T00:00:00+00:00"
}
```

### Create Order
```json
{
  "customer_id": "1",
  "order_number": "ORD-000001",
  "total_amount": "299.99",
  "status": "pending",
  "items": "[{\"product_id\":1,\"product_name\":\"Sample Product\",\"quantity\":2,\"price\":149.99,\"total\":299.98}]",
  "created_at": "2024-01-01T00:00:00+00:00"
}
```

### Update Product
```json
{
  "name": "Updated Product",
  "description": "Updated description",
  "price": 199.99,
  "created_at": "2024-01-01T00:00:00+00:00"
}
```

## 🏗️ CQRS Architecture

This API implements the CQRS pattern:

### Commands (Write Operations)
- **Database Writes**: All mutations go through the database
- **Projection Updates**: Redis projections are automatically updated
- **Event-Driven**: Uses Symfony Messenger for command handling

### Queries (Read Operations)
- **Redis Projections**: Fast reads from Redis cache
- **Denormalized Data**: Optimized for query performance
- **No Database Load**: Reads don't hit the main database

## 🔍 Expected Responses

### Success Responses
```json
{
  "products": [...],
  "total": 10000
}
```

### Error Responses
```json
{
  "error": "Product not found"
}
```

## 🚨 Important Notes

1. **Customer ID Required**: Orders require a valid customer ID
2. **Unique Constraints**: Customer emails must be unique
3. **Order Items**: Must be valid JSON array format
4. **Timestamps**: Use ISO 8601 format for dates
5. **Projection Sync**: Database writes automatically update Redis projections

## 🛠️ Troubleshooting

### Common Issues
1. **404 Errors**: Check if the entity ID exists
2. **Validation Errors**: Verify required fields are provided
3. **Redis Issues**: Ensure Redis is running and accessible
4. **Database Issues**: Check if migrations are up to date

### Debug Commands
```bash
# Rebuild projections
docker-compose exec app php bin/console app:rebuild-product-projections
docker-compose exec app php bin/console app:rebuild-customer-projections
docker-compose exec app php bin/console app:rebuild-order-projections

# Seed database
docker-compose exec app php bin/console app:seed-database

# Check Redis
docker-compose exec redis redis-cli keys "*"
```

## 📊 Performance Benefits

- **Fast Reads**: Redis projections provide sub-millisecond response times
- **Scalable Writes**: Database handles write operations efficiently
- **CQRS Separation**: Read and write operations are optimized independently
- **Event-Driven**: Asynchronous processing with Symfony Messenger

Enjoy testing the CQRS API! 🎉 