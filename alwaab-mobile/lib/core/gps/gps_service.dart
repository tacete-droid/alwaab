import 'package:geolocator/geolocator.dart';

extension CoordFormat on double {
  String toFixed(int digits) => toStringAsFixed(digits);
}

class GpsPosition {
  GpsPosition({required this.lat, required this.lng, required this.accuracy});

  final double lat;
  final double lng;
  final double accuracy;
}

class GpsService {
  Future<bool> ensurePermission() async {
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    return permission == LocationPermission.always ||
        permission == LocationPermission.whileInUse;
  }

  Future<GpsPosition?> getCurrentPosition() async {
    if (!await ensurePermission()) return null;

    final enabled = await Geolocator.isLocationServiceEnabled();
    if (!enabled) return null;

    final pos = await Geolocator.getCurrentPosition(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.high,
        timeLimit: Duration(seconds: 15),
      ),
    );

    return GpsPosition(
      lat: pos.latitude,
      lng: pos.longitude,
      accuracy: pos.accuracy,
    );
  }

  Stream<GpsPosition> watchPosition({Duration interval = const Duration(seconds: 45)}) async* {
    if (!await ensurePermission()) return;

    yield* Geolocator.getPositionStream(
      locationSettings: LocationSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 10,
        timeLimit: interval,
      ),
    ).map((p) => GpsPosition(lat: p.latitude, lng: p.longitude, accuracy: p.accuracy));
  }
}
