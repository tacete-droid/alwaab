import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_theme.dart';
import 'notification_state.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    context.read<NotificationState>().loadAll();
  }

  @override
  Widget build(BuildContext context) {
    final state = context.watch<NotificationState>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('الإشعارات'),
        actions: [
          if (state.unreadCount > 0)
            Padding(
              padding: const EdgeInsetsDirectional.only(end: 16),
              child: Center(
                child: Chip(
                  label: Text('${state.unreadCount} جديد'),
                  backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
                ),
              ),
            ),
        ],
      ),
      body: state.loading
          ? const Center(child: CircularProgressIndicator())
          : state.items.isEmpty
              ? const Center(child: Text('لا توجد إشعارات', style: TextStyle(color: Colors.white54)))
              : RefreshIndicator(
                  onRefresh: () => context.read<NotificationState>().loadAll(),
                  child: ListView.separated(
                    padding: const EdgeInsets.all(12),
                    itemCount: state.items.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (context, i) {
                      final n = state.items[i];
                      return Card(
                        color: n.isUnread ? AppTheme.primary.withValues(alpha: 0.08) : null,
                        child: ListTile(
                          leading: Text(n.icon, style: const TextStyle(fontSize: 24)),
                          title: Text(n.title, style: TextStyle(fontWeight: n.isUnread ? FontWeight.bold : FontWeight.normal)),
                          subtitle: Text(n.body, maxLines: 2, overflow: TextOverflow.ellipsis),
                          trailing: n.isUnread ? const Icon(Icons.circle, size: 10, color: AppTheme.primary) : null,
                          onTap: () {
                            if (n.isUnread) context.read<NotificationState>().markRead(n.id);
                          },
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
