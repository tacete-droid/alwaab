import '../../core/api/api_client.dart';

class AppNotification {
  AppNotification({
    required this.id,
    required this.title,
    required this.body,
    required this.category,
    required this.icon,
    required this.createdAt,
    this.readAt,
  });

  final String id;
  final String title;
  final String body;
  final String category;
  final String icon;
  final String createdAt;
  final String? readAt;

  bool get isUnread => readAt == null;

  factory AppNotification.fromJson(Map<String, dynamic> json) => AppNotification(
        id: json['id'] as String,
        title: json['title'] as String? ?? '',
        body: json['body'] as String? ?? '',
        category: json['category'] as String? ?? 'system',
        icon: json['icon'] as String? ?? '🔔',
        createdAt: json['created_at'] as String? ?? '',
        readAt: json['read_at'] as String?,
      );
}

class NotificationPollResult {
  NotificationPollResult({required this.notifications, required this.unreadCount});

  final List<AppNotification> notifications;
  final int unreadCount;
}

class NotificationRepository {
  NotificationRepository(this._api);

  final ApiClient _api;

  Future<NotificationPollResult> poll({String? since}) async {
    final res = await _api.dio.get('/notifications/poll', queryParameters: {
      if (since != null) 'since': since,
    });
    final data = res.data['data'] as Map<String, dynamic>;
    final list = (data['notifications'] as List<dynamic>)
        .map((e) => AppNotification.fromJson(e as Map<String, dynamic>))
        .toList();
    return NotificationPollResult(
      notifications: list,
      unreadCount: data['unread_count'] as int? ?? 0,
    );
  }

  Future<List<AppNotification>> list() async {
    final res = await _api.dio.get('/notifications');
    final list = res.data['data'] as List<dynamic>;
    return list.map((e) => AppNotification.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<void> markRead(String id) async {
    await _api.dio.post('/notifications/$id/read');
  }
}
