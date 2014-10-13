.. _filesystem:

Filesystem Abstraction
======================

The ``rampage\\filesystem`` component provides an OO abstraction layer for
filesystem access.

A filesystem might be a local, remote or even a virtual filesystem.


.. _filesystem.fsinterface:

FilesystemInterface
-------------------

**Interface:** ``rampage\\filesystem\\FilesystemInterface``

This interface defines basic filesystem access via the :term:`ArrayAccess` and :term:`RecursiveIterator` patterns.
It encapsulates the underlying filesystem and provides container like access. Much like `Phar`_ or `ZipArchive`_

It is not intended to provide write access.

The following classes are provided as implementation for this interface:

    * :doc:`filesystem/localfilesystem`: For fs access on disk.
    * GridFS: For accessing a MongoDB GridFS.


.. _filesystem.rw.fsinterface:

WritableFilesystemInterface
---------------------------

**Interface:** ``rampage\\filesystem\\WritableFilesystemInterface``

This interface enhances the :ref:`FilesystemInterface <filesystem.fsinterface>` by defining write capabilities which are:

    * adding files
    * deleting files
    * creating directories
    * updating the access/modified timestamps (touch)

An implementation must provide these methods and respond gracefully, when they're not supported (i.e. creating directories).

The following classes are provided as implementation for this interface:

    * :doc:`filesystem/writablelocalfilesystem`: for fs access on disk.
    * WritableGridFS: For accessing a MongoDB GridFS.


Available Implementations
-------------------------

.. toctree::
    :maxdepth: 1

    filesystem/localfilesystem
    filesystem/writablelocalfilesystem



.. _Phar: http://php.net/manual/de/class.phar.php
.. _ZipArchive: http://de1.php.net/manual/de/class.ziparchive.php
