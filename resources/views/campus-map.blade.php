@extends('layouts.app')

@section('content')
  <h1 class="text-2xl font-bold mb-4">Manage Campus Map</h1>

  <div id="map" class="h-[800px] w-full"></div>


  <script>
    function initMap() {
      const campusLocation = { lat: 7.313799064647153, lng: 125.67113865781764 };

      // Initialize the map
      const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 18.5,
        center: campusLocation,
      });

      // Add a marker
      const marker = new google.maps.Marker({
        position: campusLocation,
        map: map,
        title: "Campus Nav Main Location"
      });

      const infoWindow = new google.maps.InfoWindow({
        content: "<h3>Campus Nav Main Location</h3><p>This is the main spot of your navigation.</p>"
      });

      marker.addListener("click", () => {
        infoWindow.open(map, marker);
      });
    }
  </script>

  <!-- Google Maps Script -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAKiaFPLbjDLVbiRrT4Y8fnHeY7BCC2ApQ&callback=initMap" async defer></script>

@endsection
