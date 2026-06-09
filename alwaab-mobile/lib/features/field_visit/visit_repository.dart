import 'dart:io';

import 'package:dio/dio.dart';

import '../../core/api/api_client.dart';
import '../../core/gps/gps_service.dart';
import '../../core/offline/offline_helper.dart';
import '../../core/offline/offline_queue.dart';

class FieldVisitModel {
  FieldVisitModel({
    required this.id,
    required this.status,
    this.projectId,
    this.projectName,
    this.lat,
    this.lng,
    this.notes,
    this.visitedAt,
    this.photos = const [],
  });

  final String id;
  final String status;
  final String? projectId;
  final String? projectName;
  final double? lat;
  final double? lng;
  final String? notes;
  final String? visitedAt;
  final List<Map<String, dynamic>> photos;

  bool get isActive => status == 'in_progress';

  factory FieldVisitModel.fromJson(Map<String, dynamic> json) => FieldVisitModel(
        id: json['id'] as String,
        status: json['status'] as String? ?? '',
        projectId: json['project_id'] as String?,
        projectName: (json['project'] as Map<String, dynamic>?)?['name_ar'] as String?,
        lat: _toDouble(json['lat']),
        lng: _toDouble(json['lng']),
        notes: json['notes'] as String?,
        visitedAt: json['visited_at'] as String?,
        photos: (json['photos'] as List<dynamic>?)
                ?.map((e) => e as Map<String, dynamic>)
                .toList() ??
            [],
      );

  static double? _toDouble(dynamic v) =>
      v == null ? null : double.tryParse(v.toString());
}

class ProjectOption {
  ProjectOption({required this.id, required this.nameAr, this.location});

  final String id;
  final String nameAr;
  final String? location;

  factory ProjectOption.fromJson(Map<String, dynamic> json) => ProjectOption(
        id: json['id'] as String,
        nameAr: json['name_ar'] as String? ?? '',
        location: json['location'] as String?,
      );
}

class VisitRepository {
  VisitRepository(this._api, this._gps, this._queue);

  final ApiClient _api;
  final GpsService _gps;
  final OfflineQueue _queue;

  Future<List<FieldVisitModel>> history() async {
    final res = await _api.dio.get('/visits/history');
    final list = res.data['data'] as List<dynamic>;
    return list.map((e) => FieldVisitModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<FieldVisitModel?> active() async {
    final res = await _api.dio.get('/visits/active');
    final data = res.data['data'];
    if (data == null) return null;
    return FieldVisitModel.fromJson(data as Map<String, dynamic>);
  }

  Future<List<ProjectOption>> projects() async {
    final res = await _api.dio.get('/projects', queryParameters: {'per_page': 100});
    final list = res.data['data'] as List<dynamic>;
    return list.map((e) => ProjectOption.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<FieldVisitModel> startVisit({
    required String projectId,
    String? notes,
  }) async {
    final pos = await _gps.getCurrentPosition();
    final res = await _api.dio.post('/visits', data: {
      'project_id': projectId,
      if (pos != null) 'lat': pos.lat,
      if (pos != null) 'lng': pos.lng,
      if (notes != null && notes.isNotEmpty) 'notes': notes,
    });
    return FieldVisitModel.fromJson(res.data['data'] as Map<String, dynamic>);
  }

  /// يُرسل الموقع فوراً أو يُخزّن للمزامنة لاحقاً عند انقطاع الشبكة.
  Future<bool> updateLocation(String visitId, GpsPosition pos) async {
    try {
      await _api.dio.put('/visits/$visitId/location', data: {
        'lat': pos.lat,
        'lng': pos.lng,
      });
      return true;
    } on DioException catch (e) {
      if (!isConnectionError(e)) rethrow;
      await _queue.enqueue(OfflineAction(
        id: newActionId(),
        type: OfflineActionType.locationUpdate,
        visitId: visitId,
        createdAt: DateTime.now(),
        lat: pos.lat,
        lng: pos.lng,
      ));
      return false;
    }
  }

  /// يُرفع الصورة فوراً أو يُخزّن محلياً للمزامنة لاحقاً.
  Future<bool> uploadPhoto(String visitId, File file, GpsPosition? pos) async {
    try {
      final form = FormData.fromMap({
        'photo': await MultipartFile.fromFile(file.path, filename: 'visit.jpg'),
        if (pos != null) 'lat': pos.lat,
        if (pos != null) 'lng': pos.lng,
      });
      await _api.dio.post('/visits/$visitId/photos', data: form);
      return true;
    } on DioException catch (e) {
      if (!isConnectionError(e)) rethrow;
      final savedPath = await persistPhoto(file);
      await _queue.enqueue(OfflineAction(
        id: newActionId(),
        type: OfflineActionType.photoUpload,
        visitId: visitId,
        createdAt: DateTime.now(),
        lat: pos?.lat,
        lng: pos?.lng,
        filePath: savedPath,
      ));
      return false;
    }
  }

  /// يُنهي الزيارة فوراً أو يُخزّن الطلب للمزامنة لاحقاً.
  Future<({FieldVisitModel? visit, bool queued})> complete(String visitId, {String? notes}) async {
    try {
      final res = await _api.dio.put('/visits/$visitId/complete', data: {
        if (notes != null) 'notes': notes,
      });
      return (
        visit: FieldVisitModel.fromJson(res.data['data'] as Map<String, dynamic>),
        queued: false,
      );
    } on DioException catch (e) {
      if (!isConnectionError(e)) rethrow;
      await _queue.enqueue(OfflineAction(
        id: newActionId(),
        type: OfflineActionType.completeVisit,
        visitId: visitId,
        createdAt: DateTime.now(),
        notes: notes,
      ));
      return (visit: null, queued: true);
    }
  }

  Future<List<Map<String, dynamic>>> liveGps() async {
    final res = await _api.dio.get('/visits/live-gps');
    return (res.data['data']['visits'] as List<dynamic>)
        .map((e) => e as Map<String, dynamic>)
        .toList();
  }
}
