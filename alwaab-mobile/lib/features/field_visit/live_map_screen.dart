import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_theme.dart';
import 'visit_repository.dart';

class LiveMapScreen extends StatefulWidget {
  const LiveMapScreen({super.key});

  @override
  State<LiveMapScreen> createState() => _LiveMapScreenState();
}

class _LiveMapScreenState extends State<LiveMapScreen> {
  List<Map<String, dynamic>> _markers = [];
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _fetch();
    _timer = Timer.periodic(const Duration(seconds: 15), (_) => _fetch());
  }

  Future<void> _fetch() async {
    try {
      final data = await context.read<VisitRepository>().liveGps();
      setState(() => _markers = data);
    } catch (_) {}
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final points = _markers
        .where((m) => m['lat'] != null && m['lng'] != null)
        .map((m) => LatLng((m['lat'] as num).toDouble(), (m['lng'] as num).toDouble()))
        .toList();

    final center = points.isNotEmpty ? points.first : const LatLng(25.2048, 55.2708);

    return Scaffold(
      appBar: AppBar(
        title: const Text('تتبع GPS مباشر'),
        actions: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Center(child: Text('${_markers.length} نشطة', style: const TextStyle(fontSize: 12, color: AppTheme.success))),
          ),
        ],
      ),
      body: FlutterMap(
        options: MapOptions(initialCenter: center, initialZoom: 11),
        children: [
          TileLayer(
            urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            userAgentPackageName: 'ae.alwaab.mobile',
          ),
          MarkerLayer(
            markers: _markers.map((m) {
              final lat = (m['lat'] as num).toDouble();
              final lng = (m['lng'] as num).toDouble();
              return Marker(
                point: LatLng(lat, lng),
                width: 40,
                height: 40,
                child: const Icon(Icons.person_pin_circle, color: AppTheme.primary, size: 36),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }
}
