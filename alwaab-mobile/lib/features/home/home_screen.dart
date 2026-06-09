import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/auth/auth_state.dart';
import '../../core/theme/app_theme.dart';
import '../field_visit/visit_create_screen.dart';
import '../field_visit/visit_active_screen.dart';
import '../field_visit/visits_list_screen.dart';
import '../field_visit/live_map_screen.dart';
import '../field_visit/visit_repository.dart';
import '../notifications/notification_state.dart';
import '../notifications/notifications_screen.dart';
import '../../core/offline/sync_service.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _tab = 0;
  FieldVisitModel? _activeVisit;
  bool _loadingActive = true;

  @override
  void initState() {
    super.initState();
    _loadActive();
  }

  Future<void> _loadActive() async {
    final repo = context.read<VisitRepository>();
    final auth = context.read<AuthState>();
    if (!auth.canCreateVisit) {
      setState(() => _loadingActive = false);
      return;
    }
    try {
      final v = await repo.active();
      setState(() {
        _activeVisit = v;
        _loadingActive = false;
      });
    } catch (_) {
      setState(() => _loadingActive = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthState>();
    final notifs = context.watch<NotificationState>();
    final sync = context.watch<SyncService>();

    final pages = <Widget>[
      _DashboardTab(
        activeVisit: _activeVisit,
        loading: _loadingActive,
        onRefresh: _loadActive,
      ),
      const VisitsListScreen(),
      if (auth.canManageVisits) const LiveMapScreen() else const SizedBox.shrink(),
      _ProfileTab(),
    ];

    final navItems = <NavigationDestination>[
      const NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'الرئيسية'),
      const NavigationDestination(icon: Icon(Icons.place_outlined), selectedIcon: Icon(Icons.place), label: 'الزيارات'),
      if (auth.canManageVisits)
        const NavigationDestination(icon: Icon(Icons.map_outlined), selectedIcon: Icon(Icons.map), label: 'GPS'),
      const NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'حسابي'),
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text('ALWAAB'),
        actions: [
          if (!sync.isOnline)
            const Padding(
              padding: EdgeInsetsDirectional.only(end: 4),
              child: Icon(Icons.wifi_off, color: Colors.orange, size: 20),
            ),
          IconButton(
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationsScreen()),
            ).then((_) => notifs.loadAll()),
            icon: Badge(
              isLabelVisible: notifs.unreadCount > 0,
              label: Text('${notifs.unreadCount}'),
              child: const Icon(Icons.notifications_outlined),
            ),
          ),
        ],
      ),
      body: IndexedStack(index: _tab, children: pages),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tab,
        onDestinationSelected: (i) => setState(() => _tab = i),
        destinations: navItems,
        backgroundColor: const Color(0xFF0F172A),
        indicatorColor: AppTheme.primary.withValues(alpha: 0.2),
      ),
      floatingActionButton: auth.canCreateVisit && _activeVisit == null && _tab <= 1
          ? FloatingActionButton(
              onPressed: () async {
                await Navigator.push(context, MaterialPageRoute(builder: (_) => const VisitCreateScreen()));
                _loadActive();
              },
              child: const Icon(Icons.add),
            )
          : auth.canCreateVisit && _activeVisit != null
              ? FloatingActionButton.extended(
                  onPressed: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => VisitActiveScreen(visit: _activeVisit!)),
                  ).then((_) => _loadActive()),
                  icon: const Icon(Icons.gps_fixed),
                  label: const Text('زيارة جارية'),
                )
              : null,
    );
  }
}

class _DashboardTab extends StatelessWidget {
  const _DashboardTab({required this.activeVisit, required this.loading, required this.onRefresh});

  final FieldVisitModel? activeVisit;
  final bool loading;
  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthState>();

    return RefreshIndicator(
      onRefresh: () async => onRefresh(),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text('مرحباً ${auth.user?.displayName ?? ''}', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          if (loading)
            const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator()))
          else if (activeVisit != null)
            Card(
              child: ListTile(
                leading: const Icon(Icons.gps_fixed, color: AppTheme.primary),
                title: Text(activeVisit!.projectName ?? 'زيارة جارية'),
                subtitle: const Text('اضغط الزر السفلي لمتابعة الزيارة'),
                trailing: const Icon(Icons.chevron_left),
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => VisitActiveScreen(visit: activeVisit!)),
                ).then((_) => onRefresh()),
              ),
            )
          else if (auth.canCreateVisit)
            const Card(
              child: Padding(
                padding: EdgeInsets.all(16),
                child: Text('لا توجد زيارة جارية — ابدأ زيارة جديدة من زر +', style: TextStyle(color: Colors.white70)),
              ),
            ),
          if (auth.canManageVisits) ...[
            const SizedBox(height: 12),
            Card(
              color: AppTheme.success.withValues(alpha: 0.15),
              child: ListTile(
                leading: const Icon(Icons.radar, color: AppTheme.success),
                title: const Text('تتبع GPS الميداني'),
                subtitle: const Text('متابعة الزيارات الجارية لحظياً'),
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LiveMapScreen())),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _ProfileTab extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthState>();
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(auth.user?.displayName ?? '', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
          Text(auth.user?.email ?? '', style: const TextStyle(color: Colors.white54)),
          const SizedBox(height: 24),
          OutlinedButton.icon(
            onPressed: () => auth.logout(),
            icon: const Icon(Icons.logout),
            label: const Text('تسجيل الخروج'),
          ),
        ],
      ),
    );
  }
}
