LogPipe Version history
=======================

    [!] Improvement  [*] Milestone  [+] New feature  [-] Removed feature

    0.2.8   [+] Plugin architecture for extending the dumper.
            [+] Events implemented to facilitate writing plugins.
            [!] The status bar from the interactive dumper is now a plugin, and thus available
                in both dumpers.
            [!] A lot of rewrites and optimizations
    0.2.7   [!] The ConsoleHandler is now a fully compliant PSR logger.
            [+] Added a status bar to the interactive dumper
    0.2.6   [+] Implemented metrics logging in the dumper
            [+] Added --timeout option to dump command to limit execution time for scripting
            [!] A lot of refactoring
            [!] Implemented a cleaner dumper API
            [+] Implemented BSON serializer, requires php-mongodb to be installed
    0.2.5   [!] Reduced memory load when using the standard (non-interactive) dumper by moving
                the buffer and configuration logic to the interactive dumper.
            [+] Exceptions are now unwrapped and displayed in a more useful format.
            [!] Dumping should now lag less when a lot of data is received
    0.2.4   [!] CPU load during dumping reducded
    0.2.3   [*] The PipeV1Protocol header increased to 16 bytes.
            [+] Separated the standard dumper and the interactive dumper.
            [!] Added lots of phpdoc comments, and code cleanup.
    0.2.2   [!] Increased message header size to prevent large messages from creating corrupted
                payloads.
    0.2.1   [+] Implemented fifo-buffer to hold last N requests for quick searching.
            [+] Both Escape and Q can now be used to exit the dumper while in interactive mode.
            [*] TCP is now the default transport (tcp:127.0.0.1:6601)
    0.2     [!] Data encapsulation stuff handled by PipeV1Protocol class.
            [!] Pipe transport considered fully functional.
    0.1.5   [+] Added more unit tests.
    0.1.3   [+] Added serializers.
            [!] Updated message- and transport structure.
            [!] Improved console commands.
    0.1.2   [!] TCP transport considered functional.
            [+] Added support for using the envvar APP_ID, or the define() APP_ID to specify
                the prefix to use. Setting neither will use the hostname as app id.
    0.1     [*] Initial release.
