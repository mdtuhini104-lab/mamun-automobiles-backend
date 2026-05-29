import 'package:flutter/material.dart';
import 'services/offline_sync.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize the database and offline sync scheduler
  final syncService = OfflineSyncService();
  await syncService.initialize();
  
  runApp(const MamunAutomobilesApp());
}

class MamunAutomobilesApp extends StatelessWidget {
  const MamunAutomobilesApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Mamun Automobiles ERP',
      theme: ThemeData(
        brightness: Brightness.dark,
        primaryColor: Colors.indigo,
        useMaterial3: true,
      ),
      home: const DashboardScreen(),
    );
  }
}

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final OfflineSyncService _syncService = OfflineSyncService();
  int _pendingJobsCount = 0;

  @override
  void initState() {
    super.initState();
    _checkPendingSyncs();
  }

  Future<void> _checkPendingSyncs() async {
    final count = await _syncService.getPendingCount();
    setState(() {
      _pendingJobsCount = count;
    });
  }

  Future<void> _triggerSync() async {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Starting synchronization sync queue...')),
    );
    await _syncService.syncPendingQueue();
    await _checkPendingSyncs();
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Sync complete! All diagnostics uploaded.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Workshop Mobile Platform'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _checkPendingSyncs,
          ),
        ],
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.airport_shuttle, size: 72, color: Colors.indigoAccent),
              const SizedBox(height: 24),
              const Text(
                'Technician Offline-First Mode',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              const Text(
                'Camera scanner, offline sync storage, and diagnostic checklists ready.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey),
              ),
              const SizedBox(height: 48),
              Card(
                color: Colors.indigo.withOpacity(0.1),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: const BorderSide(color: Colors.indigo, width: 0.5),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    children: [
                      Text(
                        '$_pendingJobsCount',
                        style: const TextStyle(fontSize: 48, fontWeight: FontWeight.black, color: Colors.white),
                      ),
                      const Text(
                        'Pending Offline Sync Changes',
                        style: TextStyle(fontSize: 12, color: Colors.grey),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: _pendingJobsCount > 0 ? _triggerSync : null,
                icon: const Icon(Icons.sync),
                label: const Text('Force Sync Upload'),
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
