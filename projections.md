## Rebuild projections (Redis):

Go to http://localhost:8088/en/products/db

Go to http://localhost:8088/en/products/projection

# Move data into projection
```bash
make rebuild-projections
```




## Run load test against it

### fpm MySQL read
make k6-fpm-products-db


### read from projection
make k6-fpm-products-redis

### franken MySQL read

make k6-franken-products-db




### Product Projections
- **Entity**: `ProductProjection`
- **Repository**: `ProductProjectionRepository`
- **Service**: `ProductProjectionService`
- **Builder**: `ProductProjectionBuilder`

### Customer Projections
- **Entity**: `CustomerProjection`
- **Repository**: `CustomerProjectionRepository`
- **Service**: `CustomerProjectionService`
- **Builder**: `CustomerProjectionBuilder`

### Order Projections
- **Entity**: `OrderProjection`
- **Repository**: `OrderProjectionRepository`
- **Service**: `OrderProjectionService`
- **Builder**: `OrderProjectionBuilder`
- 
## API Endpoints

### Database Endpoints (Write Model)
```
GET /en/products/db       # List all products from database
GET /en/customers/db      # List all customers from database
GET /en/orders/db         # List all orders from database
```

### Redis Projection Endpoints (Read Model)

#### Products
- `GET /en/products/projection` - List all products from Redis projections
- `GET /en/products/projection/{id}` - Get specific product from Redis projections
- `PUT /en/products/{id}` - Update product (database + Redis projection)
- `DELETE /en/products/{id}` - Delete product (database + Redis projection)
- `POST /en/products` - Create new product (database + Redis projection)

#### Customers
- `GET /en/customers/projection` - List all customers from Redis projections
- `GET /en/customers/projection/{id}` - Get specific customer from Redis projections
- `POST /en/customers` - Create new customer (database + Redis projection)
- `DELETE /en/customers/{id}` - Delete customer (database + Redis projection)

#### Orders
- `GET /en/orders/projection` - List all orders from Redis projections
- `GET /en/orders/projection/{id}` - Get specific order from Redis projections
- `POST /en/orders` - Create new order (database + Redis projection)
- `DELETE /en/orders/{id}` - Delete order (database + Redis projection)

## Projection Management

### Rebuild Projections

**Rebuild all projections:**
```bash
make rebuild-projections
```

**Rebuild individual projections:**
```bash
make rebuild-products      # Rebuild product projections only
make rebuild-customers     # Rebuild customer projections only
make rebuild-orders        # Rebuild order projections only
```

### Database vs Redis Performance

**Test Database Performance:**
```bash
# FPM
make k6-fpm-products-db
make k6-fpm-customers-db
make k6-fpm-orders-db

# FrankenPHP
make k6-franken-products-db
make k6-franken-customers-db
make k6-franken-orders-db

# FrankenPHP Worker
make k6-franken-worker-products-db
make k6-franken-worker-customers-db
make k6-franken-worker-orders-db
```

**Test Redis Projection Performance:**
```bash
# FPM
make k6-fpm-products-redis
make k6-fpm-customers-redis
make k6-fpm-orders-redis

# FrankenPHP
make k6-franken-products-redis
make k6-franken-customers-redis
make k6-franken-orders-redis

# FrankenPHP Worker
make k6-franken-worker-products-redis
make k6-franken-worker-customers-redis
make k6-franken-worker-orders-redis
