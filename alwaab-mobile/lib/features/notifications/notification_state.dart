import 'dart:async';

import 'package:flutter/foundation.dart';

import 'notification_repository.dart';

class NotificationState extends ChangeNotifier {
  NotificationState(this._repo);

  final NotificationRepository _repo;

  int unreadCount = 0;
  List<AppNotification> items = [];
  bool loading = false;
  String? lastSince;

  Timer? _pollTimer;

  void startPolling() {
    _pollTimer?.cancel();
    poll();
    _pollTimer = Timer.periodic(const Duration(seconds: 30), (_) => poll());
  }

  void stopPolling() {
    _pollTimer?.cancel();
    _pollTimer = null;
  }

  Future<void> poll() async {
    try {
      final result = await _repo.poll(since: lastSince);
      unreadCount = result.unreadCount;
      if (result.notifications.isNotEmpty) {
        lastSince = result.notifications.first.createdAt;
      }
      notifyListeners();
    } catch (_) {}
  }

  Future<void> loadAll() async {
    loading = true;
    notifyListeners();
    try {
      items = await _repo.list();
      unreadCount = items.where((n) => n.isUnread).length;
    } catch (_) {
      items = [];
    }
    loading = false;
    notifyListeners();
  }

  Future<void> markRead(String id) async {
    try {
      await _repo.markRead(id);
      final idx = items.indexWhere((n) => n.id == id);
      if (idx >= 0) {
        items[idx] = AppNotification(
          id: items[idx].id,
          title: items[idx].title,
          body: items[idx].body,
          category: items[idx].category,
          icon: items[idx].icon,
          createdAt: items[idx].createdAt,
          readAt: DateTime.now().toIso8601String(),
        );
      }
      if (unreadCount > 0) unreadCount--;
      notifyListeners();
    } catch (_) {}
  }

  @override
  void dispose() {
    stopPolling();
    super.dispose();
  }
}
