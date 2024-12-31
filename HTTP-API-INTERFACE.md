# DataMapper API HTTP Interface

## Prefix

The default URL path prefix for the API is `/api/`. This can be customized
when generating routes. These examples will use this prefix.



## Create An Object

To create a new object, use the POST method with the URL path ending with the
object type and the object as JSON in the request body. The response will
contain the new object as it is stored on the server. It may contain more
information than what was sent to the server. A succesful response will have
a `201 Created` response status.

### Example Request
```
POST /api/Object/
Accept: application/json
Content-Type: application/json

{
    "object_id": 1,
    "name": "Object One"
}
```

### Example Response
```
HTTP 201 OK
Content-Type: application/json

{
    "object_id": 1,
    "name": "Object One",
    "active": true,
    "create_datetime": "2021-05-01 12:00:00",
    "update_datetime": null
}
```



## Get Single Object

To get a single object from the API, use the following URL path.

### Example Request
```
GET /api/Object/1/
Accept: application/json
```

### Example Response
```
HTTP 200 OK
Content-Type: application/json

{
    "object_id": 1,
    "name": "Object One",
    "active": true,
    "create_datetime": "2021-05-01 12:00:00",
    "update_datetime": null
}
```



## Update An Object

To update a new object, use the PUTT method with the URL path ending with the
object id and the object as JSON in the request body. The response will
contain the new object as it is stored on the server. It may contain more
information than what was sent to the server. A succesful response will have
a `200 OK` response status. Only fields sent in the JSON body will be changed.
Any field not included will retain it's current value on the server.

### Example Request
```
PUT /api/Object/1/
Accept: application/json
Content-Type: application/json

{
    "object_id": 1,
    "name": "Object One Updated",
}
```

### Example Response
```
HTTP 201 OK
Content-Type: application/json

{
    "object_id": 1,
    "name": "Object One Updated",
    "active": true,
    "create_datetime": "2021-05-01 12:00:00",
    "update_datetime": "2021-05-01 12:01:00"
}
```



## Delete An Object

To delete an object, use the DELETE method and the full URL to the object.

### Example Request
```
DELETE /api/Object/1/
Accept: application/json
```

### Example Response
```
HTTP 200 OK
Content-Type: application/json

```



## Search Objects

To search for objects and control order in which they are returned, the
special `_search` endpoint is used. The query format is a JSON structure sent
via a POST request.

### Query DSL

*start* - Integer for starting record. Default 0

*limit* - Integer for number of records to return. Default 100

*sort* - Object of fields and sort direction (asc or desc)

*filter* - Object of fields and values to filter. A scalar value means "equal
to". An array of scalar values means "equal to one of". For other comparisons,
the value should be an object with one entry where the key is the comparion
operator (`>`, `>=`, `<`, `<=`, `between`) and the value is the value to
compare the field against. In the case of `between`, the value should be an
array with two values. All filters are treated as `AND`. Comparing `OR` across
fields is not supported.

### Example Request
```
POST /api/Object/_search/
Accept: application/json
Content-Type: application/json

{
    "filter": {
        "active": true,
        "create_datetime": {
            ">=": "2021-05-01 12:00:00"
        }
    },
    "start": 0,
    "limit": 100,
    "sort": {
        "create_datetime": "asc"
    }

}
```

### Example Response
```
HTTP 200 OK
Content-Type: application/json

[
    {
        "object_id": 1,
        "name": "Object One Updated",
        "active": true,
        "create_datetime": "2021-05-01 12:00:00",
        "update_datetime": "2021-05-01 12:01:00"
    },
    {
        "object_id": 2,
        "name": "Object Two",
        "active": true,
        "create_datetime": "2021-05-01 12:05:00",
        "update_datetime": null
    }
]
```




## Get Multiple Objects

*DEPRECATED* - Use the search endpoint instead.
