<!DOCTYPE HTML>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Web Crawler</title>
    </head>

    <body>
    <form action="{{route('crawler.search')}}">
        <input type="text" name="postcode" id="postcode" placeholder="Postcode" value="{{$postcode}}">
        <button type="submit">Search</button>
    </form>
        @if(!empty($properties))
            <table border="1">
                <thead>
                <tr>
                    <th>Address</th>
                    <th>Type</th>
                    <th>Price</th>
                </tr>
                <tbody>
                @foreach($properties as $property)
                <tr>
                    <td>{{$property['address']}}</td>
                    <td>{{$property['type']}}</td>
                    <td>{{$property['price']}}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </body>
</html>
