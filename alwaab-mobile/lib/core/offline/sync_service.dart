import 'dart:async';
import 'dart:io';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../api/api_client.dart';
import 'offline_queue.dart';

class SyncService extends ChangeNotifier {
  SyncService(this._api, this._queue) {
    _listenConnectivity();
    syncPending();
  }

  final ApiClient _api;
  final OfflineQueue _queue;

  bool _online = true;
  bool _syncing = false;
  int _pending = 0;

  bool get isOnline => _online;
  bool get isSyncing => _syncing;
  int get pendingCount => _pending;

  StreamSubscription<List<ConnectivityResult>>? _connSub;

  void _listenConnectivity() {
    _connSub = Connectivity().onConnectivityChanged.listen((results) {
      final wasOffline = !_online;
      _online = results.any((r) => r != ConnectivityResult.none);
      notifyListeners();
      if (wasOffline && _online) syncPending();
    });

    Connectivity().checkConnectivity().then((results) {
      _online = results.any((r) => r != ConnectivityResult.none);
      _refreshPending();
    });
  }

  Future<void> _refreshPending() async {
    _pending = await _queue.count();
    notifyListeners();
  }

  Future<void> syncPending() async {
    if (_syncing) return;

    final items = await _queue.all();
    if (items.isEmpty) {
      _pending = 0;
      notifyListeners();
      return;
    }

    _syncing = true;
    notifyListeners();

    for (final action in items) {
      try {
        await _process(action);
        await _queue.remove(action.id);
      } on DioException catch (e) {
        if (_isConnectionError(e)) break;
        await _queue.remove(action.id);
      } catch (_) {
        break;
      }
    }

    _syncing = false;
    await _refreshPending();
  }

  Future<void> _process(OfflineAction action) async {
    switch (action.type) {
      case OfflineActionType.locationUpdate:
        await _api.dio.put('/visits/${action.visitId}/location', data: {
          'lat': action.lat,
          'lng': action.lng,
        });
      case OfflineActionType.photoUpload:
        final file = File(action.filePath!);
        if (!await file.exists()) return;
        final form = FormData.fromMap({
          'photo': await MultipartFile.fromFile(file.path, filename: 'visit.jpg'),
          if (action.lat != null) 'lat': action.lat,
          if (action.lng != null) 'lng': action.lng,
        });
        await _api.dio.post('/visits/${action.visitId}/photos', data: form);
      case OfflineActionType.completeVisit:
        await _api.dio.put('/visits/${action.visitId}/complete', data: {
          if (action.notes != null) 'notes': action.notes,
        });
    }
  }

  bool _isConnectionError(DioException e) =>
      e.type == DioExceptionType.connectionError ||
      e.type == DioExceptionType.connectionTimeout ||
      e.type == DioExceptionType.sendTimeout ||
      e.type == DioExceptionType.receiveTimeout;

  @override
  void dispose() {
    _connSub?.cancel();
    super.dispose();
  }
}
