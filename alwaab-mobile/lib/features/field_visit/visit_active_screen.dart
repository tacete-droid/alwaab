import 'dart:async';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../core/gps/gps_service.dart';
import '../../core/offline/sync_service.dart';
import '../../core/theme/app_theme.dart';
import 'visit_repository.dart';

class VisitActiveScreen extends StatefulWidget {
  const VisitActiveScreen({super.key, required this.visit});

  final FieldVisitModel visit;

  @override
  State<VisitActiveScreen> createState() => _VisitActiveScreenState();
}

class _VisitActiveScreenState extends State<VisitActiveScreen> {
  late FieldVisitModel _visit;
  String _gpsLabel = '—';
  String? _statusMsg;
  StreamSubscription<GpsPosition>? _gpsSub;
  final _notes = TextEditingController();
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _visit = widget.visit;
    _notes.text = _visit.notes ?? '';
    _startGpsTracking();
  }

  void _startGpsTracking() {
    final gps = context.read<GpsService>();
    final repo = context.read<VisitRepository>();

    _gpsSub = gps.watchPosition().listen((pos) async {
      setState(() => _gpsLabel = '${pos.lat.toFixed(5)}, ${pos.lng.toFixed(5)}');
      try {
        final sent = await repo.updateLocation(_visit.id, pos);
        if (mounted) {
          setState(() => _statusMsg = sent ? null : 'GPS محفوظ — سيُزامَن عند عودة الشبكة');
        }
      } catch (_) {}
    });
  }

  Future<void> _takePhoto() async {
    final picker = ImagePicker();
    final file = await picker.pickImage(source: ImageSource.camera, imageQuality: 85);
    if (file == null) return;

    setState(() => _busy = true);
    try {
      final gps = context.read<GpsService>();
      final repo = context.read<VisitRepository>();
      final pos = await gps.getCurrentPosition();
      final sent = await repo.uploadPhoto(_visit.id, File(file.path), pos);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(sent ? 'تم رفع الصورة' : 'الصورة محفوظة — ستُرفع عند عودة الشبكة'),
        ));
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('فشل رفع الصورة')));
      }
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _complete() async {
    setState(() => _busy = true);
    try {
      final repo = context.read<VisitRepository>();
      final result = await repo.complete(_visit.id, notes: _notes.text);
      if (result.queued) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('طلب الإنهاء محفوظ — سيُنفَّذ عند عودة الشبكة')),
          );
          Navigator.pop(context, true);
        }
      } else if (mounted) {
        Navigator.pop(context, true);
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('فشل إنهاء الزيارة')));
      }
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  void dispose() {
    _gpsSub?.cancel();
    _notes.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final sync = context.watch<SyncService>();

    return Scaffold(
      appBar: AppBar(title: Text(_visit.projectName ?? 'زيارة ميدانية')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (!sync.isOnline)
              Card(
                color: Colors.orange.withValues(alpha: 0.15),
                child: const ListTile(
                  leading: Icon(Icons.wifi_off, color: Colors.orange),
                  title: Text('وضع عدم الاتصال'),
                  subtitle: Text('البيانات تُحفظ محلياً وتُزامَن تلقائياً'),
                ),
              ),
            if (sync.pendingCount > 0)
              Card(
                color: AppTheme.primary.withValues(alpha: 0.1),
                child: ListTile(
                  leading: sync.isSyncing
                      ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Icon(Icons.cloud_upload, color: AppTheme.primary),
                  title: Text('${sync.pendingCount} عنصر بانتظار المزامنة'),
                  trailing: IconButton(
                    icon: const Icon(Icons.refresh),
                    onPressed: sync.isSyncing ? null : () => sync.syncPending(),
                  ),
                ),
              ),
            Card(
              child: ListTile(
                leading: const Icon(Icons.gps_fixed, color: AppTheme.primary),
                title: const Text('GPS نشط'),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(_gpsLabel, style: const TextStyle(fontFamily: 'monospace')),
                    if (_statusMsg != null)
                      Text(_statusMsg!, style: const TextStyle(color: Colors.orange, fontSize: 12)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            OutlinedButton.icon(
              onPressed: _busy ? null : _takePhoto,
              icon: const Icon(Icons.camera_alt, size: 28),
              label: const Padding(
                padding: EdgeInsets.symmetric(vertical: 12),
                child: Text('التقط صورة ميدانية', style: TextStyle(fontSize: 16)),
              ),
            ),
            const SizedBox(height: 16),
            TextField(controller: _notes, maxLines: 3, decoration: const InputDecoration(labelText: 'ملاحظات نهائية')),
            const Spacer(),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: AppTheme.success),
              onPressed: _busy ? null : _complete,
              child: const Padding(
                padding: EdgeInsets.symmetric(vertical: 12),
                child: Text('إنهاء الزيارة', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
