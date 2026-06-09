import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

enum OfflineActionType { locationUpdate, photoUpload, completeVisit }

class OfflineAction {
  OfflineAction({
    required this.id,
    required this.type,
    required this.visitId,
    required this.createdAt,
    this.lat,
    this.lng,
    this.filePath,
    this.notes,
  });

  final String id;
  final OfflineActionType type;
  final String visitId;
  final DateTime createdAt;
  final double? lat;
  final double? lng;
  final String? filePath;
  final String? notes;

  Map<String, dynamic> toJson() => {
        'id': id,
        'type': type.name,
        'visit_id': visitId,
        'created_at': createdAt.toIso8601String(),
        if (lat != null) 'lat': lat,
        if (lng != null) 'lng': lng,
        if (filePath != null) 'file_path': filePath,
        if (notes != null) 'notes': notes,
      };

  factory OfflineAction.fromJson(Map<String, dynamic> json) => OfflineAction(
        id: json['id'] as String,
        type: OfflineActionType.values.byName(json['type'] as String),
        visitId: json['visit_id'] as String,
        createdAt: DateTime.parse(json['created_at'] as String),
        lat: (json['lat'] as num?)?.toDouble(),
        lng: (json['lng'] as num?)?.toDouble(),
        filePath: json['file_path'] as String?,
        notes: json['notes'] as String?,
      );
}

class OfflineQueue {
  static const _key = 'offline_visit_queue';

  Future<List<OfflineAction>> all() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_key);
    if (raw == null || raw.isEmpty) return [];

    final list = jsonDecode(raw) as List<dynamic>;
    return list.map((e) => OfflineAction.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<int> count() async => (await all()).length;

  Future<void> enqueue(OfflineAction action) async {
    final items = await all();
    items.add(action);
    await _save(items);
  }

  Future<void> remove(String id) async {
    final items = await all();
    items.removeWhere((a) => a.id == id);
    await _save(items);
  }

  Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_key);
  }

  Future<void> _save(List<OfflineAction> items) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
      _key,
      jsonEncode(items.map((e) => e.toJson()).toList()),
    );
  }
}
