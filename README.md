# Group Quota

Allow setting a disk quota for an entire group.

## Usage

Group quota can only be configured trough the command line or rest api, no admin UI is currently available.

### OCC commandline Api

#### Get the quota for a group

```bash
occ groupquota:get Test
```

#### Get the used space for a group

```bash
occ groupquota:used Test
```

#### Set the quota for a group

```bash
occ groupquota:set Test 2GB
```

#### Lists all configured quotas

```bash
occ groupquota:list
```

All commands accept a `--format`(`-f`) option to format their output in a human readable format.

### OCS Rest API

#### Get the quota and used space for a group

```bash
curl -u admin:admin -H 'OCS-APIRequest: true' https://example.com/apps/groupquota/quota/Test'
```

```xml
<?xml version="1.0"?>
<ocs>
 <meta>
  <status>ok</status>
  <statuscode>100</statuscode>
  <message>OK</message>
  <totalitems></totalitems>
  <itemsperpage></itemsperpage>
 </meta>
 <data>
  <quota_bytes>2147483648</quota_bytes>
  <quota_human>2 GB</quota_human>
  <used_bytes>855380973</used_bytes>
  <used_human>815.8 MB</used_human>
  <used_relative>39.83</used_relative>
 </data>
</ocs>
```

*Note*: as with all OCS requests the response can be json formatted by sending an `Accept: application/json` header

#### Set the quota for a group

Set the quota of group "Test" to 2GB

```bash
curl -u admin:admin -H 'OCS-APIRequest: true' https://example.com/apps/groupquota/quota/Test -X POST -d 'quota=2GB'
```

The new quota information will also be returned in the same format as from a `GET` request.

## Limitations

Only one group with quota set per user is supported, behavior for users with more then one group with quota set is undefined.
