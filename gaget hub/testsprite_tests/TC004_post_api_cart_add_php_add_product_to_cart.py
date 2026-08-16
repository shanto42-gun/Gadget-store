import requests

BASE_URL = "http://localhost:80/gaget hub"
TIMEOUT = 30

def test_post_api_cart_add_php_add_product_to_cart():
    session = requests.Session()
    try:
        # Login to authenticate and get session cookie
        login_payload = {
            "email": "testuser@example.com",
            "password": "TestPassword123!"
        }
        login_resp = session.post(
            f"{BASE_URL}/pages/login.php",
            json=login_payload,
            timeout=TIMEOUT
        )
        assert login_resp.status_code == 200, f"Login failed: {login_resp.text}"

        # Fetch products to find one in-stock and one out-of-stock
        products_resp = requests.get(
            f"{BASE_URL}/api/products/get_products.php?page=1&limit=50",
            timeout=TIMEOUT
        )
        assert products_resp.status_code == 200, f"Failed to fetch products: {products_resp.text}"
        products_data = products_resp.json()
        products = products_data.get("products") or products_data.get("data") or []
        assert products, "No products found in product list"

        # Identify in-stock and out-of-stock products
        in_stock_product = None
        out_of_stock_product = None
        for product in products:
            # Assuming product has fields: id, stock or in_stock or quantity
            # Try common fields to determine stock
            stock_qty = None
            for key in ("stock", "quantity", "in_stock"):
                stock_qty = product.get(key)
                if stock_qty is not None:
                    break
            if stock_qty is None:
                # If none present, assume in stock
                stock_qty = 1
            if isinstance(stock_qty, int) and stock_qty > 0 and in_stock_product is None:
                in_stock_product = product
            elif (isinstance(stock_qty, int) and stock_qty == 0) and out_of_stock_product is None:
                out_of_stock_product = product
            if in_stock_product and out_of_stock_product:
                break

        # If no product clearly out of stock found, skip out-of-stock test
        # If no product clearly in stock, raise
        assert in_stock_product is not None, "No in-stock product found for testing"

        # Test adding an in-stock product to cart
        add_instock_payload = {
            "product_id": in_stock_product["id"],
            "quantity": 1
        }
        add_instock_resp = session.post(
            f"{BASE_URL}/api/cart/add.php",
            json=add_instock_payload,
            timeout=TIMEOUT
        )
        assert add_instock_resp.status_code == 200, f"Add in-stock product failed: {add_instock_resp.text}"
        cart_summary = add_instock_resp.json()
        # Validate that cart summary contains items and subtotal keys
        assert "items" in cart_summary and isinstance(cart_summary["items"], list), "Cart summary missing items"
        assert "subtotal" in cart_summary, "Cart summary missing subtotal"

        # Test adding an out-of-stock product to cart if available
        if out_of_stock_product:
            add_outstock_payload = {
                "product_id": out_of_stock_product["id"],
                "quantity": 1
            }
            add_outstock_resp = session.post(
                f"{BASE_URL}/api/cart/add.php",
                json=add_outstock_payload,
                timeout=TIMEOUT
            )
            assert add_outstock_resp.status_code == 409, "Expected 409 Conflict for out-of-stock product"
            error_resp = add_outstock_resp.json()
            error_msg = error_resp.get("error") or ""
            assert "out of stock" in error_msg.lower(), f"Unexpected error message for out-of-stock: {error_msg}"

    finally:
        # Clear the cart after test to clean state (if API exists)
        try:
            session.post(f"{BASE_URL}/api/cart/remove.php", timeout=TIMEOUT)
        except:
            pass

test_post_api_cart_add_php_add_product_to_cart()