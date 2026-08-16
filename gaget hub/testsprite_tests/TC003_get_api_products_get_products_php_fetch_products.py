import requests
from requests.exceptions import RequestException

BASE_URL = "http://localhost:80"
ENDPOINT = "/api/products/get_products.php"
TIMEOUT = 30

def test_get_products_fetch_variants():
    session = requests.Session()

    def make_request(params, expected_status=200):
        url = BASE_URL + ENDPOINT
        try:
            response = session.get(url, params=params, timeout=TIMEOUT)
        except RequestException as e:
            assert False, f"Request failed: {e}"
        assert response.status_code == expected_status, f"Expected status {expected_status}, got {response.status_code}"
        return response

    # 1. Test default pagination: page=1, limit=20
    resp = make_request({"page": "1", "limit": "20"}, 200)
    try:
        json_data = resp.json()
    except Exception:
        assert False, "Response not JSON or invalid on default pagination"
    assert "products" in json_data and isinstance(json_data["products"], list), "Missing or invalid 'products' array"
    assert "metadata" in json_data and isinstance(json_data["metadata"], dict), "Missing or invalid 'metadata'"
    metadata = json_data["metadata"]
    assert metadata.get("page") == 1, "Metadata page should be 1"
    assert metadata.get("limit") == 20, "Metadata limit should be 20"
    assert len(json_data["products"]) <= 20, "Returned products exceed limit 20"

    # 2. Test filter by category=phones and sort=price_asc
    resp = make_request({"category": "phones", "sort": "price_asc"}, 200)
    try:
        json_data = resp.json()
    except Exception:
        assert False, "Response not JSON or invalid on category filter"
    products = json_data.get("products", [])
    assert isinstance(products, list), "'products' should be a list"
    for product in products:
        assert "category" in product, "Product missing 'category' field"
        assert product["category"].lower() == "phones", f"Product category expected 'phones' got '{product['category']}'"
    if len(products) > 1:
        prices = [float(p.get("price", 0)) for p in products]
        assert prices == sorted(prices), "Products not sorted by price ascending"

    # 3. Test invalid page parameter page=-1 returns 400 Bad Request with validation error
    resp = make_request({"page": "-1"}, expected_status=400)
    try:
        json_data = resp.json()
        assert "error" in json_data or "message" in json_data, "Expected error message in response"
    except Exception:
        assert False, "Response not JSON or missing error message on invalid page param"

    # 4. Test missing or invalid extra parameters: e.g. invalid sort field
    resp = make_request({"sort": "unknown_sort"}, 200)
    try:
        json_data = resp.json()
    except Exception:
        assert False, "Response not JSON or invalid on unknown sort param"
    assert "products" in json_data and isinstance(json_data["products"], list), "Missing or invalid 'products' on unknown sort"

    # 5. Test zero or negative limit handling (expect 400)
    resp = make_request({"limit": "0"}, expected_status=400)
    try:
        json_data = resp.json()
        assert "error" in json_data or "message" in json_data, "Expected error message for limit=0"
    except Exception:
        assert False, "Response not JSON or missing error message on limit=0"

    resp = make_request({"limit": "-5"}, expected_status=400)
    try:
        json_data = resp.json()
        assert "error" in json_data or "message" in json_data, "Expected error message for negative limit"
    except Exception:
        assert False, "Response not JSON or missing error message on negative limit"


test_get_products_fetch_variants()
