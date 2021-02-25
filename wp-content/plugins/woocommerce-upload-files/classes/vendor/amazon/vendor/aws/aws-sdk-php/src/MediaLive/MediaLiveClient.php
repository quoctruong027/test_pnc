<?php
namespace Aws\MediaLive;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Elemental MediaLive** service.
 * @method \Aws\Result batchUpdateSchedule(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchUpdateScheduleAsync(array $args = [])
 * @method \Aws\Result createChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createChannelAsync(array $args = [])
 * @method \Aws\Result createInput(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createInputAsync(array $args = [])
 * @method \Aws\Result createInputSecurityGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createInputSecurityGroupAsync(array $args = [])
 * @method \Aws\Result createMultiplex(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createMultiplexAsync(array $args = [])
 * @method \Aws\Result createMultiplexProgram(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createMultiplexProgramAsync(array $args = [])
 * @method \Aws\Result createTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createTagsAsync(array $args = [])
 * @method \Aws\Result deleteChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteChannelAsync(array $args = [])
 * @method \Aws\Result deleteInput(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteInputAsync(array $args = [])
 * @method \Aws\Result deleteInputSecurityGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteInputSecurityGroupAsync(array $args = [])
 * @method \Aws\Result deleteMultiplex(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteMultiplexAsync(array $args = [])
 * @method \Aws\Result deleteMultiplexProgram(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteMultiplexProgramAsync(array $args = [])
 * @method \Aws\Result deleteReservation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteReservationAsync(array $args = [])
 * @method \Aws\Result deleteSchedule(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteScheduleAsync(array $args = [])
 * @method \Aws\Result deleteTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteTagsAsync(array $args = [])
 * @method \Aws\Result describeChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeChannelAsync(array $args = [])
 * @method \Aws\Result describeInput(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeInputAsync(array $args = [])
 * @method \Aws\Result describeInputDevice(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeInputDeviceAsync(array $args = [])
 * @method \Aws\Result describeInputDeviceThumbnail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeInputDeviceThumbnailAsync(array $args = [])
 * @method \Aws\Result describeInputSecurityGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeInputSecurityGroupAsync(array $args = [])
 * @method \Aws\Result describeMultiplex(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeMultiplexAsync(array $args = [])
 * @method \Aws\Result describeMultiplexProgram(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeMultiplexProgramAsync(array $args = [])
 * @method \Aws\Result describeOffering(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeOfferingAsync(array $args = [])
 * @method \Aws\Result describeReservation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeReservationAsync(array $args = [])
 * @method \Aws\Result describeSchedule(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeScheduleAsync(array $args = [])
 * @method \Aws\Result listChannels(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listChannelsAsync(array $args = [])
 * @method \Aws\Result listInputDevices(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInputDevicesAsync(array $args = [])
 * @method \Aws\Result listInputSecurityGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInputSecurityGroupsAsync(array $args = [])
 * @method \Aws\Result listInputs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInputsAsync(array $args = [])
 * @method \Aws\Result listMultiplexPrograms(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listMultiplexProgramsAsync(array $args = [])
 * @method \Aws\Result listMultiplexes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listMultiplexesAsync(array $args = [])
 * @method \Aws\Result listOfferings(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listOfferingsAsync(array $args = [])
 * @method \Aws\Result listReservations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listReservationsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result purchaseOffering(array $args = [])
 * @method \GuzzleHttp\Promise\Promise purchaseOfferingAsync(array $args = [])
 * @method \Aws\Result startChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startChannelAsync(array $args = [])
 * @method \Aws\Result startMultiplex(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startMultiplexAsync(array $args = [])
 * @method \Aws\Result stopChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopChannelAsync(array $args = [])
 * @method \Aws\Result stopMultiplex(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopMultiplexAsync(array $args = [])
 * @method \Aws\Result updateChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateChannelAsync(array $args = [])
 * @method \Aws\Result updateChannelClass(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateChannelClassAsync(array $args = [])
 * @method \Aws\Result updateInput(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateInputAsync(array $args = [])
 * @method \Aws\Result updateInputDevice(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateInputDeviceAsync(array $args = [])
 * @method \Aws\Result updateInputSecurityGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateInputSecurityGroupAsync(array $args = [])
 * @method \Aws\Result updateMultiplex(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateMultiplexAsync(array $args = [])
 * @method \Aws\Result updateMultiplexProgram(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateMultiplexProgramAsync(array $args = [])
 * @method \Aws\Result updateReservation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateReservationAsync(array $args = [])
 */
class MediaLiveClient extends AwsClient {}
