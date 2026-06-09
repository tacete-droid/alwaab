import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/gps/gps_service.dart';
import '../../core/theme/app_theme.dart';
import 'visit_active_screen.dart';
import 'visit_repository.dart';

class VisitCreateScreen extends StatefulWidget {
  const VisitCreateScreen({super.key});

  @override
  State<VisitCreateScreen> createState() => _VisitCreateScreenState();
}

class _VisitCreateScreenState extends State<VisitCreateScreen> {
  List<ProjectOption> _projects = [];
  String? _projectId;
  String _gpsLabel = 'جاري تحديد الموقع...';
  bool _gpsReady = false;
  bool _loading = true;
  bool _submitting = false;
  final _notes = TextEditingController();

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    final repo = context.read<VisitRepository>();
    final gps = context.read<GpsService>();
    final projects = await repo.projects();
    final pos = await gps.getCurrentPosition();
    setState(() {
      _projects = projects;
      _loading = false;
      if (pos != null) {
        _gpsReady = true;
        _gpsLabel = '${pos.lat.toFixed(5)}, ${pos.lng.toFixed(5)}';
      } else {
        _gpsLabel = 'تعذّر تحديد GPS';
      }
    });
  }

  Future<void> _submit() async {
    if (_projectId == null) return;
    setState(() => _submitting = true);
    try {
      final repo = context.read<VisitRepository>();
      final visit = await repo.startVisit(projectId: _projectId!, notes: _notes.text);
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => VisitActiveScreen(visit: visit)),
      );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('فشل بدء الزيارة')));
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('زيارة جديدة')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  DropdownButtonFormField<String>(
                    value: _projectId,
                    decoration: const InputDecoration(labelText: 'المشروع'),
                    items: _projects
                        .map((p) => DropdownMenuItem(value: p.id, child: Text(p.nameAr)))
                        .toList(),
                    onChanged: (v) => setState(() => _projectId = v),
                  ),
                  const SizedBox(height: 16),
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Row(
                        children: [
                          Icon(Icons.gps_fixed, color: _gpsReady ? AppTheme.success : Colors.white38),
                          const SizedBox(width: 8),
                          Expanded(child: Text(_gpsLabel, style: const TextStyle(fontFamily: 'monospace', fontSize: 13))),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(controller: _notes, maxLines: 3, decoration: const InputDecoration(labelText: 'ملاحظات')),
                  const Spacer(),
                  FilledButton(
                    onPressed: _submitting || _projectId == null ? null : _submit,
                    child: _submitting
                        ? const CircularProgressIndicator()
                        : const Text('بدء الزيارة', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
    );
  }
}
