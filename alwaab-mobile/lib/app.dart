import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';

import 'core/api/api_client.dart';
import 'core/auth/auth_repository.dart';
import 'core/auth/auth_state.dart';
import 'core/gps/gps_service.dart';
import 'core/offline/offline_queue.dart';
import 'core/offline/sync_service.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/login_screen.dart';
import 'features/field_visit/visit_repository.dart';
import 'features/home/home_screen.dart';
import 'features/notifications/notification_repository.dart';
import 'features/notifications/notification_state.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class AlwaabApp extends StatelessWidget {
  const AlwaabApp({super.key});

  @override
  Widget build(BuildContext context) {
    const storage = FlutterSecureStorage();
    final api = ApiClient(storage);
    final authRepo = AuthRepository(api);
    final offlineQueue = OfflineQueue();
    final visitRepo = VisitRepository(api, GpsService(), offlineQueue);
    final notifRepo = NotificationRepository(api);

    return MultiProvider(
      providers: [
        Provider.value(value: api),
        Provider.value(value: GpsService()),
        Provider.value(value: offlineQueue),
        Provider.value(value: visitRepo),
        Provider.value(value: notifRepo),
        ChangeNotifierProvider(create: (_) => AuthState(authRepo)..bootstrap()),
        ChangeNotifierProvider(create: (_) => SyncService(api, offlineQueue)),
        ChangeNotifierProvider(create: (_) => NotificationState(notifRepo)),
      ],
      child: MaterialApp(
        title: 'ALWAAB',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.dark(),
        locale: const Locale('ar'),
        supportedLocales: const [Locale('ar'), Locale('en')],
        localizationsDelegates: const [
          GlobalMaterialLocalizations.delegate,
          GlobalWidgetsLocalizations.delegate,
          GlobalCupertinoLocalizations.delegate,
        ],
        builder: (context, child) => Directionality(textDirection: TextDirection.rtl, child: child!),
        home: const _Root(),
      ),
    );
  }
}

class _Root extends StatefulWidget {
  const _Root();

  @override
  State<_Root> createState() => _RootState();
}

class _RootState extends State<_Root> {
  AuthState? _auth;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _onAuthReady());
  }

  void _onAuthReady() {
    _auth = context.read<AuthState>();
    _auth!.addListener(_authChanged);
    if (_auth!.isLoggedIn) _startServices();
  }

  void _startServices() {
    context.read<NotificationState>().startPolling();
    context.read<SyncService>().syncPending();
  }

  void _authChanged() {
    if (_auth == null) return;
    if (_auth!.isLoggedIn) {
      _startServices();
    } else {
      context.read<NotificationState>().stopPolling();
    }
  }

  @override
  void dispose() {
    _auth?.removeListener(_authChanged);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthState>();

    if (auth.loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return auth.isLoggedIn ? const HomeScreen() : const LoginScreen();
  }
}
